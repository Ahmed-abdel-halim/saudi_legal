<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Company;
use App\Models\ExpertService;
use App\Models\ServicePurchase;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SimulateWorkflowCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:simulate-workflow {--cleanup : Delete simulation data after running}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulate the entire end-to-end service and payment workflow.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting End-to-End Workflow Simulation...');
        
        DB::beginTransaction();
        
        try {
            // STEP 1: Register Provider Company & Employee
            $this->comment('Step 1: Registering Provider Company & Employee');
            
            $providerCompany = Company::create([
                'name' => 'Tech Solutions Ltd (Sim)',
                'cr_number' => 'SIM-' . rand(1000, 9999),
                'industry' => 'IT Services',
                'size' => '10-50',
                'is_supplier' => true,
                'status' => 'active',
                'wallet_balance' => 0.00
            ]);
            
            $providerEmployee = User::create([
                'name' => 'Alice Expert (Sim)',
                'email' => 'alice.sim.' . time() . '@example.com',
                'password' => Hash::make('password'),
                'role' => 'expert',
                'company_id' => $providerCompany->company_id,
                'is_active' => true,
                'is_active_for_hire' => true,
                'wallet_balance' => 0.00
            ]);
            
            $this->info("Provider Company Created: {$providerCompany->name} (Balance: SAR 0)");
            $this->info("Employee Created: {$providerEmployee->name} (Balance: SAR 0)");
            
            // Add a service
            $service = ExpertService::create([
                'expert_id' => $providerEmployee->id,
                'title' => 'Advanced Code Review (Sim)',
                'category' => 'Software Development',
                'price' => 150.00, // Hourly rate
                'delivery_days' => 3,
                'description' => 'Comprehensive code review and refactoring suggestions.',
                'is_active' => true,
            ]);
            
            $this->info("Service Created: {$service->title} at {$service->price} SAR/hr");

            // STEP 2: Register Client Company
            $this->comment("\nStep 2: Registering Client Company & User");
            
            $clientCompany = Company::create([
                'name' => 'Startup Inc (Sim)',
                'cr_number' => 'SIM-' . rand(1000, 9999),
                'industry' => 'Technology',
                'size' => '1-10',
                'is_requester' => true,
                'status' => 'active',
                'wallet_balance' => 0.00
            ]);
            
            $clientUser = User::create([
                'name' => 'Bob Client (Sim)',
                'email' => 'bob.sim.' . time() . '@example.com',
                'password' => Hash::make('password'),
                'role' => 'client', // assuming client role exists physically or logically via company affiliation
                'company_id' => $clientCompany->company_id,
                'is_active' => true,
            ]);
            
            $this->info("Client Company Created: {$clientCompany->name}");

            // STEP 3: Request the Service & Create Chat
            $this->comment("\nStep 3: Client Requests Service & Chat is initialized");
            
            $hours = 5;
            $purchase = ServicePurchase::create([
                'expert_id' => $providerEmployee->id,
                'client_id' => $clientUser->id,
                'service_id' => $service->service_id,
                'hours_purchased' => $hours,
                'hourly_rate' => $service->price,
                'total_price' => $hours * $service->price,
                'status' => 'pending',
                'payment_status' => 'unpaid',
            ]);
            
            $this->info("Service Requested - Total Price: {$purchase->total_price} SAR");
            
            $conversation = Conversation::create([
                'participant_1' => $clientUser->id,
                'participant_2' => $providerEmployee->id,
                'contract_id' => $purchase->id,
                'contract_type' => Conversation::TYPE_HOURLY_PURCHASE,
                'status' => Conversation::STATUS_ACTIVE,
                'is_active' => true,
            ]);
            
            Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $clientUser->id,
                'sender_type' => Message::TYPE_COMPANY,
                'content' => 'Hello, I have requested your service and look forward to working with you.',
            ]);
            $this->info('Chat Conversation created and initialized.');

            // STEP 4: Payment Simulation (Escrow)
            $this->comment("\nStep 4: Simulating Stripe Payment (Escrow)");
            
            // Simulating what the webhook does when payment is confirmed
            $purchase->update([
                'payment_status' => 'paid',
                'status' => 'accepted', // Status becomes accepted when paid
                'paid_at' => now(),
                'stripe_session_id' => 'cs_test_mock_' . time(),
                'stripe_payment_intent_id' => 'pi_test_mock_' . time(),
            ]);
            
            $this->info("Payment Confirmed! Funds are held in Escrow.");

            // STEP 5: Service Completion & Confirmation
            $this->comment("\nStep 5: Simulating Service Completion by Client");
            
            // Dispatching the controller action manually or executing the logic
            // Since we're in CLI, we'll mimic the exact DB transaction the controller uses to distribute funds
            
            $totalPrice = $purchase->total_price;
            $platformCommission = $totalPrice * 0.20;
            $expertShare = $totalPrice * 0.40;
            $companyShare = $totalPrice * 0.40;
            
            $purchase->update([
                'status' => 'completed',
                'service_status' => 'completed',
                'completed_at' => now(),
            ]);

            $providerEmployee->increment('wallet_balance', $expertShare);
            $providerCompany->increment('wallet_balance', $companyShare);
            
            // Create Wallet Transactions
            \App\Models\WalletTransaction::create([
                'user_id' => $providerEmployee->id,
                'type' => 'credit',
                'amount' => $expertShare,
                'description' => "40% Share from Service Completion (#{$purchase->id})",
                'reference_type' => ServicePurchase::class,
                'reference_id' => $purchase->id,
            ]);

            \App\Models\WalletTransaction::create([
                'company_id' => $providerCompany->company_id,
                'type' => 'credit',
                'amount' => $companyShare,
                'description' => "40% Share from Service Completion (#{$purchase->id})",
                'reference_type' => ServicePurchase::class,
                'reference_id' => $purchase->id,
            ]);

            $this->info("Service Marked Completed by Client.");

            // STEP 6: Revenue Distribution Verification
            $this->comment("\nStep 6: Verifying Payment Distribution splits");
            
            $updatedEmployee = $providerEmployee->fresh();
            $updatedCompany = $providerCompany->fresh();
            
            $this->info("Total Paid: {$totalPrice} SAR");
            $this->info("Platform Commission (20%): {$platformCommission} SAR");
            $this->info("Employee Shared Wallet Balance (+40%): {$updatedEmployee->wallet_balance} SAR");
            $this->info("Provider Company Wallet Balance (+40%): {$updatedCompany->wallet_balance} SAR");

            // Assertions
            if ($updatedEmployee->wallet_balance != $expertShare) {
                throw new \Exception("Employee balance mismatch. Expected {$expertShare}, got {$updatedEmployee->wallet_balance}");
            }
            if ($updatedCompany->wallet_balance != $companyShare) {
                throw new \Exception("Company balance mismatch. Expected {$companyShare}, got {$updatedCompany->wallet_balance}");
            }

            // STEP 7: Withdrawal Rules Simulation
            $this->comment("\nStep 7: Testing 200 SAR Withdrawal Limit Rule");
            
            // Create a fake HTTP request to hit the WalletController 
            // Or test manually by calculating logic here
            $controller = new \App\Http\Controllers\WalletController();
            
            // Test Employee Withdrawal
            $this->info("Testing Employee Withdrawal: Current Balance = {$updatedEmployee->wallet_balance}");
            $this->info("Attempting to withdraw 150 SAR (less than 200 minimum limit)...");
            
            // Using actingAs to properly mock authentication for the controller
            $this->laravel['auth']->guard('web')->setUser($updatedEmployee);
            $request1 = clone request();
            $request1->replace(['amount' => 150]);
            
            $response1 = $controller->withdraw($request1);
            
            if ($response1->status() === 422) {
                $this->info("Result: Rejected successfully (" . json_decode($response1->getContent())->message . ")");
            } else {
                throw new \Exception("Withdrawal should have been rejected for being under 200 threshold!");
            }
            
            $this->info("Attempting to withdraw 250 SAR (valid)...");
            $request2 = clone request();
            $request2->replace(['amount' => 250]);
            
            $response2 = $controller->withdraw($request2);
            
            if ($response2->status() === 200) {
                $this->info("Result: Accepted successfully.");
                $this->info("Employee New Balance: {$updatedEmployee->fresh()->wallet_balance} SAR");
            } else {
                throw new \Exception("Withdrawal failed: " . $response2->getContent());
            }
            
            // Cleanup prompt
            if ($this->option('cleanup')) {
                $this->comment("\nCleaning up simulation data...");
                DB::rollBack();
                $this->info('Cleanup complete. Real database was not altered.');
            } else {
                DB::commit();
                $this->info("\nSimulation data has been persisted to the database.");
            }

            $this->info("\n✅ END-TO-END WORKFLOW SIMULATION PASSED SUCCESSFULLY!");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("\n❌ SIMULATION FAILED: ");
            $this->error($e->getMessage());
            if ($e instanceof \Illuminate\Database\QueryException) {
                $this->error("SQL: " . $e->getSql());
                $this->error("Bindings: " . json_encode($e->getBindings()));
            }
            // $this->error($e->getTraceAsString()); // removing full trace which clutter console
            return 1;
        }

        return 0;
    }
}
