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
    // ─── Checkout ────────────────────────────────────────────────────────────

    /**
     * Create a Stripe Checkout Session and redirect the client.
     */
    public function checkout(ServicePurchase $purchase)
    {
        // Only the purchasing client may initiate payment
        if (auth()->id() !== (int) $purchase->client_id) {
            abort(403, 'You are not authorised to pay for this order.');
        }

        // Already paid — send straight to success
        if ($purchase->payment_status === 'paid') {
            return redirect()->route('payment.success')
                ->with('info', 'This order has already been paid.');
        }

        $purchase->load('service');
        $service = $purchase->service;
        $stripe  = new StripeClient(config('services.stripe.secret'));

        $session = $stripe->checkout->sessions->create([
            'mode'        => 'payment',
            'line_items'  => [[
                'price_data' => [
                    'currency'     => 'sar',
                    'unit_amount'  => (int) round($purchase->total_price * 100), // halalas
                    'product_data' => [
                        'name'        => $service ? $service->title : 'Expert Service',
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
            'success_url' => route('payment.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => route('payment.cancel', $purchase->id),
        ]);

        // Persist session ID for webhook cross-reference
        $purchase->update(['stripe_session_id' => $session->id]);

        return redirect($session->url, 303);
    }

    // ─── Success / Cancel UI ─────────────────────────────────────────────────

    /**
     * UI-only success page — does NOT mark the order as paid.
     * Payment confirmation comes exclusively from the webhook.
     */
    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');
        $purchase  = null;

        if ($sessionId) {
            $purchase = ServicePurchase::where('stripe_session_id', $sessionId)->first();
        }

        return view('payment.success', compact('purchase'));
    }

    /**
     * UI-only cancel page.
     * Guards: only the buyer can view it; already-paid orders redirect to success.
     */
    public function cancel(ServicePurchase $purchase)
    {
        // Ownership guard
        if (auth()->id() !== (int) $purchase->client_id) {
            abort(403, 'You are not authorised to view this page.');
        }

        // If it was already paid (webhook arrived before user landed here), redirect to success
        if ($purchase->payment_status === 'paid') {
            return redirect()->route('payment.success')
                ->with('info', 'Your payment was actually successful!');
        }

        $purchase->load('service', 'expert');

        return view('payment.cancel', compact('purchase'));
    }

    // ─── Webhook (Financial Source of Truth) ─────────────────────────────────

    /**
     * Receive and verify Stripe webhook events.
     * This is the ONLY place that marks a payment as confirmed.
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

        // Handle each event type
        switch ($event->type) {
            case 'checkout.session.completed':
                $this->handleCheckoutCompleted($event->data->object);
                break;

            default:
                // Log unhandled events for debugging
                Log::info('Stripe webhook: unhandled event type', ['type' => $event->type]);
        }

        return response()->json(['status' => 'ok'], 200);
    }

    // ─── Private Handlers ────────────────────────────────────────────────────

    private function handleCheckoutCompleted(object $session): void
    {
        $purchaseId = $session->metadata->purchase_id ?? null;

        if (! $purchaseId) {
            Log::error('Stripe webhook: missing purchase_id in metadata', ['session' => $session->id]);
            return;
        }

        $purchase = ServicePurchase::find($purchaseId);

        if (! $purchase) {
            Log::error('Stripe webhook: ServicePurchase not found', ['purchase_id' => $purchaseId]);
            return;
        }

        // ── Idempotency: skip if already processed ──────────────────────────
        if ($purchase->payment_status === 'paid') {
            Log::info('Stripe webhook: duplicate event, purchase already paid', [
                'purchase_id' => $purchaseId,
                'session_id'  => $session->id,
            ]);
            return;
        }

        // ── Only confirm if Stripe reports it as paid ───────────────────────
        if ($session->payment_status !== 'paid') {
            Log::warning('Stripe webhook: checkout.session.completed but payment_status is not paid', [
                'purchase_id'    => $purchaseId,
                'payment_status' => $session->payment_status,
            ]);
            return;
        }

        // ── Atomic update inside a transaction ──────────────────────────────
        DB::transaction(function () use ($purchase, $session) {
            $purchase->update([
                'payment_status'           => 'paid',
                'status'                   => 'paid',
                'paid_at'                  => now(),
                'stripe_payment_intent_id' => $session->payment_intent,
            ]);

            // Notify the expert that payment has been secured
            $expert = $purchase->expert;
            if ($expert) {
                $expert->notify(new ExpertPaymentReceivedNotification($purchase));
            }

            Log::info('Stripe webhook: payment confirmed', [
                'purchase_id'       => $purchase->id,
                'stripe_session_id' => $session->id,
                'amount'            => $session->amount_total,
            ]);
        });
    }
}
