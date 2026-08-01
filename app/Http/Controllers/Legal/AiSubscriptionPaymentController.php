<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Models\AiPackage;
use App\Models\AiSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class AiSubscriptionPaymentController extends Controller
{
    private function stripe(): StripeClient
    {
        return new StripeClient(config('services.stripe.secret'));
    }

    // ─── Show Public Pricing Page ──────────────────────────────────────────────

    public function pricingPage()
    {
        $packages = AiPackage::active()->get();

        // Current user subscription if any
        $currentSubscription = null;
        if (auth()->check()) {
            $currentSubscription = AiSubscription::where('user_id', auth()->id())
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('ends_at')->orWhere('ends_at', '>', now());
                })
                ->with('package')
                ->latest()
                ->first();

            // Fallback: If no active sub found, check if a pending sub was already paid at Stripe
            if (!$currentSubscription) {
                $pendingSub = AiSubscription::where('user_id', auth()->id())
                    ->where('status', 'pending')
                    ->whereNotNull('stripe_session_id')
                    ->latest()
                    ->first();

                if ($pendingSub) {
                    try {
                        $stripe = $this->stripe();
                        $session = $stripe->checkout->sessions->retrieve($pendingSub->stripe_session_id);
                        if ($session && $session->payment_status === 'paid') {
                            $this->handleCheckoutCompleted($session);
                            $currentSubscription = $pendingSub->fresh(['package']);
                        }
                    } catch (\Exception $e) {
                        Log::warning('AI Pricing Page: Error verifying pending sub with Stripe', ['error' => $e->getMessage()]);
                    }
                }
            }
        }

        return view('legal.ai_packages', compact('packages', 'currentSubscription'));
    }

    // ─── Initiate Checkout ─────────────────────────────────────────────────────

    public function checkout(Request $request, AiPackage $package)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('info', 'يجب تسجيل الدخول أولاً للاشتراك في الباقة.');
        }

        if ($package->is_free || $package->price == 0) {
            return $this->activateFreePackage($package);
        }

        $stripe = $this->stripe();
        $user = auth()->user();

        // Expire any existing pending session for this package
        $existingPending = AiSubscription::where('user_id', $user->id)
            ->where('ai_package_id', $package->id)
            ->where('status', 'pending')
            ->whereNotNull('stripe_session_id')
            ->first();

        if ($existingPending) {
            try {
                $stripe->checkout->sessions->expire($existingPending->stripe_session_id);
            } catch (\Exception $e) {
                // Ignore if already expired
            }
            $existingPending->update(['status' => 'cancelled']);
        }

        // Build period display
        $periodLabel = match ($package->billing_period) {
            'monthly'  => 'شهرياً',
            'yearly'   => 'سنوياً',
            'lifetime' => 'مدى الحياة',
            default    => $package->billing_period,
        };

        $session = $stripe->checkout->sessions->create([
            'mode'       => 'payment',
            'line_items' => [[
                'price_data' => [
                    'currency'     => 'sar',
                    'unit_amount'  => (int) round($package->price * 100),
                    'product_data' => [
                        'name'        => 'رديف AI — ' . $package->name,
                        'description' => 'اشتراك ' . $periodLabel . ' — ' . $package->query_limit_display,
                    ],
                ],
                'quantity' => 1,
            ]],
            'metadata' => [
                'package_id' => $package->id,
                'user_id'    => $user->id,
                'type'       => 'ai_subscription',
            ],
            'customer_email' => $user->email,
            'expires_at'     => now()->addMinutes(60)->timestamp,
            'success_url'    => route('ai.subscription.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'     => route('ai.packages'),
        ]);

        // Record pending subscription
        AiSubscription::create([
            'user_id'          => $user->id,
            'ai_package_id'    => $package->id,
            'status'           => 'pending',
            'stripe_session_id' => $session->id,
            'amount_paid'      => $package->price,
            'currency'         => 'SAR',
        ]);

        Log::info('AI Subscription: Stripe checkout session created', [
            'user_id'    => $user->id,
            'package_id' => $package->id,
            'session_id' => $session->id,
        ]);

        return redirect($session->url, 303);
    }

    // ─── Activate Free Package ─────────────────────────────────────────────────

    private function activateFreePackage(AiPackage $package)
    {
        $user = auth()->user();

        // Cancel any existing free sub first
        AiSubscription::where('user_id', $user->id)
            ->where('ai_package_id', $package->id)
            ->update(['status' => 'cancelled']);

        $ends = $package->billing_period === 'lifetime' ? null : now()->addMonth();

        AiSubscription::create([
            'user_id'       => $user->id,
            'ai_package_id' => $package->id,
            'status'        => 'active',
            'amount_paid'   => 0,
            'currency'      => 'SAR',
            'starts_at'     => now(),
            'ends_at'       => $ends,
        ]);

        return redirect()->route('legal_assistant.public')
                         ->with('success', '🎉 تم تفعيل الباقة المجانية! يمكنك البدء في الاستفسار الآن.');
    }

    // ─── Success Page ──────────────────────────────────────────────────────────

    public function success(Request $request)
    {
        $sessionId    = $request->query('session_id');
        $subscription = null;

        if ($sessionId) {
            $subscription = AiSubscription::where('stripe_session_id', $sessionId)
                ->with('package')
                ->first();

            // Instant Activation Fallback: If sub is pending, check directly with Stripe API
            if ($subscription && $subscription->status === 'pending') {
                try {
                    $stripe = $this->stripe();
                    $session = $stripe->checkout->sessions->retrieve($sessionId);
                    if ($session && $session->payment_status === 'paid') {
                        $this->handleCheckoutCompleted($session);
                        $subscription->refresh();
                    }
                } catch (\Exception $e) {
                    Log::warning('AI Subscription Success Page: Could not verify Stripe session', [
                        'session_id' => $sessionId,
                        'error'      => $e->getMessage(),
                    ]);
                }
            }
        }

        return view('legal.ai_subscription_success', compact('subscription'));
    }

    // ─── Stripe Webhook ───────────────────────────────────────────────────────

    public function webhook(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        // نستخدم نفس webhook_secret الموجود مسبقاً بدون حاجة لإعداد Webhook جديد على Stripe
        $secret    = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (SignatureVerificationException $e) {
            Log::warning('AI Subscription Webhook: Invalid signature');
            return response('Invalid signature', 400);
        }

        Log::info('AI Subscription Webhook received: ' . $event->type);

        match ($event->type) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($event->data->object),
            'checkout.session.expired'   => $this->handleCheckoutExpired($event->data->object),
            default                      => null,
        };

        return response('OK', 200);
    }

    /**
     * Public entry point called from the main PaymentController webhook.
     * This allows reusing the same Stripe webhook endpoint for both
     * service purchases and AI subscriptions (distinguished by metadata.type).
     */
    public function handleWebhookEvent(object $session, string $eventType): void
    {
        match ($eventType) {
            'completed' => $this->handleCheckoutCompleted($session),
            'expired'   => $this->handleCheckoutExpired($session),
            default     => null,
        };
    }

    private function handleCheckoutCompleted($session): void
    {
        // Only handle our AI subscriptions
        if (($session->metadata->type ?? '') !== 'ai_subscription') {
            return;
        }

        $packageId = $session->metadata->package_id ?? null;
        $userId    = $session->metadata->user_id ?? null;

        if (!$packageId || !$userId) return;

        $package = AiPackage::find($packageId);
        if (!$package) return;

        // Expire existing active subscriptions for user
        AiSubscription::where('user_id', $userId)
            ->where('status', 'active')
            ->update(['status' => 'expired']);

        // Calculate period
        $ends = match ($package->billing_period) {
            'yearly'   => now()->addYear(),
            'lifetime' => null,
            default    => now()->addMonth(),
        };

        // Activate or update the pending subscription
        $sub = AiSubscription::where('stripe_session_id', $session->id)->first();

        if ($sub) {
            $sub->update([
                'status'                   => 'active',
                'stripe_payment_intent_id' => $session->payment_intent,
                'starts_at'                => now(),
                'ends_at'                  => $ends,
                'amount_paid'              => ($session->amount_total ?? 0) / 100,
            ]);
        } else {
            AiSubscription::create([
                'user_id'                  => $userId,
                'ai_package_id'            => $packageId,
                'status'                   => 'active',
                'stripe_session_id'        => $session->id,
                'stripe_payment_intent_id' => $session->payment_intent,
                'amount_paid'              => ($session->amount_total ?? 0) / 100,
                'currency'                 => strtoupper($session->currency ?? 'SAR'),
                'starts_at'                => now(),
                'ends_at'                  => $ends,
            ]);
        }

        Log::info('AI Subscription activated via webhook', [
            'user_id'    => $userId,
            'package_id' => $packageId,
            'session_id' => $session->id,
        ]);
    }

    private function handleCheckoutExpired($session): void
    {
        if (($session->metadata->type ?? '') !== 'ai_subscription') return;

        AiSubscription::where('stripe_session_id', $session->id)
            ->where('status', 'pending')
            ->update(['status' => 'cancelled']);

        Log::info('AI Subscription checkout expired', ['session_id' => $session->id]);
    }
}
