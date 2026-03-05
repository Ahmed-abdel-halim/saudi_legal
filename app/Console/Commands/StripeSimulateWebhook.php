<?php

namespace App\Console\Commands;

use App\Http\Controllers\PaymentController;
use App\Models\ServicePurchase;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class StripeSimulateWebhook extends Command
{
    protected $signature   = 'stripe:simulate-webhook {purchase_id : The ID of the ServicePurchase}';
    protected $description = 'Simulate a Stripe checkout.session.completed webhook event locally (dev only).';

    public function handle(): int
    {
        if (! app()->isLocal()) {
            $this->error('This command can only run in the local environment.');
            return self::FAILURE;
        }

        $purchaseId = (int) $this->argument('purchase_id');
        $purchase   = ServicePurchase::find($purchaseId);

        if (! $purchase) {
            $this->error("ServicePurchase #{$purchaseId} not found.");
            return self::FAILURE;
        }

        if ($purchase->payment_status === 'paid') {
            $this->warn("Purchase #{$purchaseId} is already paid. Skipping.");
            return self::SUCCESS;
        }

        $this->info("Simulating checkout.session.completed for Purchase #{$purchaseId}...");

        // Build a minimal fake Stripe session object matching what Stripe sends
        $fakeSession = (object) [
            'id'             => $purchase->stripe_session_id ?? 'cs_test_simulated_' . $purchaseId,
            'payment_intent' => 'pi_simulated_' . $purchaseId,
            'payment_status' => 'paid',
            'amount_total'   => (int) round($purchase->total_price * 100),
            'metadata'       => (object) [
                'purchase_id' => $purchaseId,
                'client_id'   => $purchase->client_id,
                'expert_id'   => $purchase->expert_id,
            ],
        ];

        // Call the private handler via reflection so we don't skip validation
        $controller = new PaymentController();
        $reflection  = new \ReflectionMethod(PaymentController::class, 'handleCheckoutCompleted');
        $reflection->setAccessible(true);
        $reflection->invoke($controller, $fakeSession);

        $purchase->refresh();

        if ($purchase->payment_status === 'paid') {
            $this->info("✅ Purchase #{$purchaseId} is now PAID.");
            $this->line("   paid_at:                  {$purchase->paid_at}");
            $this->line("   stripe_payment_intent_id: {$purchase->stripe_payment_intent_id}");
            $this->newLine();
            $this->info("Expert notification queued (check notifications table).");
        } else {
            $this->error("Payment status was NOT updated. Check logs for details.");
        }

        return self::SUCCESS;
    }
}
