<?php

namespace App\Http\Controllers;

use App\Models\ServicePurchase;
use App\Notifications\ExpertPaymentReceivedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;

class PaymentController extends Controller
{
    // ─── Shared Stripe Client ─────────────────────────────────────────────────

    private function stripe(): StripeClient
    {
        return new StripeClient(config('services.stripe.secret'));
    }

    // ─── Checkout ─────────────────────────────────────────────────────────────

    /**
     * Create a Stripe Checkout Session and redirect the client.
     *
     * Before opening a new session, any previously stored (but unpaid) session
     * is expired via the Stripe API so the client cannot complete a stale session.
     */
    public function checkout(ServicePurchase $purchase)
    {
        // Ownership guard
        if (auth()->id() !== (int) $purchase->client_id) {
            abort(403, 'You are not authorised to pay for this order.');
        }

        // Already paid — send straight to success
        if (in_array($purchase->payment_status, ['paid', 'escrow'])) {
            return redirect()->route('payment.success')
                ->with('info', 'This order has already been paid.');
        }

        $stripe = $this->stripe();
        $purchase->load('service');

        // ── Expire any existing open Stripe session ─────────────────────────
        if ($purchase->stripe_session_id) {
            $this->tryExpireSession($stripe, $purchase->stripe_session_id, $purchase->id);
            $purchase->update(['stripe_session_id' => null]);
        }

        // ── Create a fresh Checkout Session ─────────────────────────────────
        $service = $purchase->service;

        $session = $stripe->checkout->sessions->create([
            'mode'       => 'payment',
            'line_items' => [[
                'price_data' => [
                    'currency'     => 'sar',
                    'unit_amount'  => (int) round($purchase->total_price * 100), // halalas
                    'product_data' => [
                        'name'        => $service?->title ?? 'Expert Service',
                        'description' => 'Service purchase #' . $purchase->id,
                    ],
                ],
                'quantity' => 1,
            ]],
            'metadata' => [
                'purchase_id' => $purchase->id,
                'client_id'   => $purchase->client_id,
                'expert_id'   => $purchase->expert_id,
            ],
            // expires_at: Stripe default is 24h; set to 30 min for shorter window
            'expires_at'  => now()->addMinutes(30)->timestamp,
            'success_url' => route('payment.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => route('payment.cancel', $purchase->id),
        ]);

        $purchase->update(['stripe_session_id' => $session->id]);

        Log::info('Stripe: new checkout session created', [
            'purchase_id' => $purchase->id,
            'session_id'  => $session->id,
            'expires_at'  => $session->expires_at,
        ]);

        return redirect($session->url, 303);
    }

    // ─── Success / Cancel UI ──────────────────────────────────────────────────

    /**
     * UI-only success page.
     * Payment confirmation comes exclusively from the webhook — never here.
     */
    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');
        $purchase  = null;

        if ($sessionId) {
            $purchase = ServicePurchase::where('stripe_session_id', $sessionId)
                ->with('service', 'expert')
                ->first();
        }

        return view('payment.success', compact('purchase'));
    }

    /**
     * UI-only cancel page.
     * Guards: only the buyer can view it; already-paid orders redirect to success.
     */
    public function cancel(ServicePurchase $purchase)
    {
        if (auth()->id() !== (int) $purchase->client_id) {
            abort(403, 'You are not authorised to view this page.');
        }

        if (in_array($purchase->payment_status, ['paid', 'escrow'])) {
            return redirect()->route('payment.success')
                ->with('info', 'Your payment was actually confirmed!');
        }

        $purchase->load('service', 'expert');

        return view('payment.cancel', compact('purchase'));
    }

    // ─── Webhook (Financial Source of Truth) ──────────────────────────────────

    /**
     * Receive and verify all Stripe webhook events.
     * This is the ONLY place that alters payment state.
     */
    public function webhook(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\UnexpectedValueException $e) {
            Log::error('Stripe webhook: invalid payload', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (SignatureVerificationException $e) {
            Log::error('Stripe webhook: invalid signature', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $sessionObj = $event->data->object;

        switch ($event->type) {

            // ── Payment confirmed ────────────────────────────────────────────
            case 'checkout.session.completed':
                $this->handleCheckoutCompleted($sessionObj);
                break;

            // ── Session expired (30-min window elapsed, or we expired it manually) ──
            case 'checkout.session.expired':
                $this->handleCheckoutExpired($sessionObj);
                break;

            // ── Async payment methods: payment failed after session completed ─
            case 'checkout.session.async_payment_failed':
                $this->handleAsyncPaymentFailed($sessionObj);
                break;

            // ── Async payment succeeded (e.g. bank transfer) ─────────────────
            case 'checkout.session.async_payment_succeeded':
                $this->handleCheckoutCompleted($sessionObj);
                break;

            default:
                Log::info('Stripe webhook: unhandled event', ['type' => $event->type]);
        }

        // Always return 200 so Stripe does not retry handled events
        return response()->json(['status' => 'ok'], 200);
    }

    // ─── Private Event Handlers ───────────────────────────────────────────────

    /**
     * checkout.session.completed — payment confirmed by Stripe.
     */
    private function handleCheckoutCompleted(object $session): void
    {
        $purchase = $this->resolvePurchase($session);
        if (! $purchase) return;

        // Idempotency: skip if already processed
        if (in_array($purchase->payment_status, ['paid', 'escrow'])) {
            Log::info('Stripe webhook: duplicate completed event, skipping', [
                'purchase_id' => $purchase->id,
                'session_id'  => $session->id,
            ]);
            return;
        }

        if ($session->payment_status !== 'paid') {
            Log::warning('Stripe webhook: completed but payment_status not paid', [
                'purchase_id'    => $purchase->id,
                'payment_status' => $session->payment_status,
            ]);
            return;
        }

        DB::transaction(function () use ($purchase, $session) {
            $purchase->update([
                'payment_status'           => 'paid',
                'status'                   => 'paid',
                'paid_at'                  => now(),
                'stripe_payment_intent_id' => $session->payment_intent,
            ]);

            $expert = $purchase->expert;
            if ($expert) {
                $expert->notify(new ExpertPaymentReceivedNotification($purchase));
            }

            Log::info('Stripe webhook: payment confirmed', [
                'purchase_id' => $purchase->id,
                'session_id'  => $session->id,
                'amount'      => $session->amount_total,
            ]);
        });
    }

    /**
     * checkout.session.expired — session timed out or was manually expired.
     * Reset stripe_session_id so a fresh session can be created on next retry.
     */
    private function handleCheckoutExpired(object $session): void
    {
        $purchase = $this->resolvePurchase($session);
        if (! $purchase) return;

        // Only reset if this session is still the active one (not superseded)
        if ($purchase->stripe_session_id !== $session->id) {
            Log::info('Stripe webhook: expired session is already superseded, skipping', [
                'purchase_id'       => $purchase->id,
                'expired_session'   => $session->id,
                'current_session'   => $purchase->stripe_session_id,
            ]);
            return;
        }

        // Don't touch paid or escrow orders
        if (in_array($purchase->payment_status, ['paid', 'escrow'])) {
            return;
        }

        $purchase->update(['stripe_session_id' => null]);

        Log::info('Stripe webhook: session expired, stripe_session_id cleared', [
            'purchase_id' => $purchase->id,
            'session_id'  => $session->id,
        ]);
    }

    /**
     * checkout.session.async_payment_failed — async method (e.g. bank) failed.
     * Reset session ID so client can retry with a new session.
     */
    private function handleAsyncPaymentFailed(object $session): void
    {
        $purchase = $this->resolvePurchase($session);
        if (! $purchase) return;

        if (in_array($purchase->payment_status, ['paid', 'escrow'])) {
            return;
        }

        $purchase->update(['stripe_session_id' => null]);

        Log::warning('Stripe webhook: async payment failed, session cleared', [
            'purchase_id' => $purchase->id,
            'session_id'  => $session->id,
        ]);
    }

    // ─── Utilities ────────────────────────────────────────────────────────────

    /**
     * Resolve a ServicePurchase from a Stripe session's metadata.
     */
    private function resolvePurchase(object $session): ?ServicePurchase
    {
        $purchaseId = $session->metadata->purchase_id ?? null;

        if (! $purchaseId) {
            Log::error('Stripe webhook: missing purchase_id in metadata', [
                'session_id' => $session->id,
            ]);
            return null;
        }

        $purchase = ServicePurchase::find($purchaseId);

        if (! $purchase) {
            Log::error('Stripe webhook: ServicePurchase not found', [
                'purchase_id' => $purchaseId,
                'session_id'  => $session->id,
            ]);
            return null;
        }

        return $purchase;
    }

    /**
     * Attempt to expire an existing Stripe session via the API.
     * Silently swallows errors (session may already be expired/completed).
     */
    private function tryExpireSession(StripeClient $stripe, string $sessionId, int $purchaseId): void
    {
        try {
            $existing = $stripe->checkout->sessions->retrieve($sessionId);

            // Only open sessions can be expired
            if ($existing->status === 'open') {
                $stripe->checkout->sessions->expire($sessionId);
                Log::info('Stripe: expired stale session before creating new one', [
                    'purchase_id' => $purchaseId,
                    'session_id'  => $sessionId,
                ]);
            }
        } catch (\Exception $e) {
            // Session may already be expired/completed — safe to ignore
            Log::info('Stripe: could not expire session (already closed?)', [
                'purchase_id' => $purchaseId,
                'session_id'  => $sessionId,
                'reason'      => $e->getMessage(),
            ]);
        }
    }
}
