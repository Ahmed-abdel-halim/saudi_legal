<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Project;
use App\Models\ProjectOffer;
use App\Models\ServicePurchase;
use App\Models\User;
use App\Services\ChatService;
use App\Services\ContractService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected $company;
    protected $expert;
    protected $chatService;
    protected $contractService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = User::factory()->create([
            'role' => 'company',
            'company_id' => 1,
        ]);

        $this->expert = User::factory()->create([
            'role' => 'expert',
        ]);

        $this->chatService = app(ChatService::class);
        $this->contractService = app(ContractService::class);
    }

    /** @test */
    public function offer_acceptance_creates_conversation()
    {
        $project = Project::factory()->create([
            'requester_company_id' => $this->company->company_id,
            'status' => 'posted',
        ]);

        $offer = ProjectOffer::create([
            'project_id' => $project->project_id,
            'expert_id' => $this->expert->id,
            'price' => 1000,
            'delivery_time_days' => 7,
            'status' => 'pending',
        ]);

        $this->actingAs($this->company)
             ->post("/requests/accept-offer/{$offer->id}")
             ->assertRedirect();

        $this->assertDatabaseHas('conversations', [
            'contract_type' => 'offer',
            'contract_id' => $offer->id,
            'participant_1' => $this->company->id,
            'participant_2' => $this->expert->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('project_offers', [
            'id' => $offer->id,
            'status' => 'accepted',
            'service_status' => 'awaiting_start',
        ]);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => Conversation::where('contract_id', $offer->id)->first()->id,
            'sender_type' => 'system',
        ]);
    }

    /** @test */
    public function service_lifecycle_completes_successfully()
    {
        // Create offer and conversation
        $offer = ProjectOffer::create([
            'project_id' => 1,
            'expert_id' => $this->expert->id,
            'price' => 1000,
            'delivery_time_days' => 7,
            'status' => 'accepted',
            'service_status' => 'awaiting_start',
        ]);

        $conversation = Conversation::create([
            'contract_type' => 'offer',
            'contract_id' => $offer->id,
            'participant_1' => $this->company->id,
            'participant_2' => $this->expert->id,
            'status' => 'active',
        ]);

        // Step 1: Expert starts service
        $this->contractService->startService('offer', $offer->id, $this->expert->id);

        $offer->refresh();
        $this->assertEquals('in_progress', $offer->service_status);
        $this->assertNotNull($offer->started_at);

        // Step 2: Expert finishes service
        $this->contractService->finishService('offer', $offer->id, $this->expert->id);

        $offer->refresh();
        $this->assertEquals('awaiting_confirmation', $offer->service_status);
        $this->assertNotNull($offer->finished_at);

        // Step 3: Company confirms delivery
        $this->contractService->confirmDelivery('offer', $offer->id, $this->company->id);

        $offer->refresh();
        $conversation->refresh();

        $this->assertEquals('completed', $offer->service_status);
        $this->assertNotNull($offer->completed_at);
        $this->assertEquals('closed', $conversation->status);

        // Verify expert metrics updated
        $this->expert->refresh();
        $this->assertEquals(1, $this->expert->completed_contracts);
        $this->assertEquals(100, $this->expert->completion_rate);
    }

    /** @test */
    public function dispute_can_be_opened_and_resolved()
    {
        $offer = ProjectOffer::create([
            'project_id' => 1,
            'expert_id' => $this->expert->id,
            'price' => 1000,
            'delivery_time_days' => 7,
            'status' => 'accepted',
            'service_status' => 'in_progress',
            'started_at' => now(),
        ]);

        $conversation = Conversation::create([
            'contract_type' => 'offer',
            'contract_id' => $offer->id,
            'participant_1' => $this->company->id,
            'participant_2' => $this->expert->id,
            'status' => 'active',
        ]);

        // Open dispute
        $this->contractService->openDispute(
            'offer',
            $offer->id,
            $this->company->id,
            'Quality issues'
        );

        $offer->refresh();
        $this->assertEquals('disputed', $offer->service_status);

        // Verify expert metrics
        $this->expert->refresh();
        $this->assertEquals(1, $this->expert->disputed_contracts);
    }

    /** @test */
    public function hourly_purchase_acceptance_creates_conversation()
    {
        $purchase = ServicePurchase::create([
            'expert_id' => $this->expert->id,
            'client_id' => $this->company->id,
            'service_id' => 1,
            'hours_purchased' => 10,
            'hourly_rate' => 50,
            'total_price' => 500,
            'status' => 'pending',
        ]);

        $this->actingAs($this->expert)
             ->post("/dashboard/expert/purchase/{$purchase->id}/accept")
             ->assertRedirect();

        $this->assertDatabaseHas('conversations', [
            'contract_type' => 'hourly_purchase',
            'contract_id' => $purchase->id,
            'participant_1' => $this->company->id,
            'participant_2' => $this->expert->id,
        ]);

        $this->assertDatabaseHas('service_purchases', [
            'id' => $purchase->id,
            'status' => 'accepted',
            'service_status' => 'awaiting_start',
        ]);
    }

    /** @test */
    public function unauthorized_user_cannot_access_conversation()
    {
        $otherUser = User::factory()->create(['role' => 'expert']);

        $conversation = Conversation::create([
            'contract_type' => 'offer',
            'contract_id' => 1,
            'participant_1' => $this->company->id,
            'participant_2' => $this->expert->id,
            'status' => 'active',
        ]);

        $this->actingAs($otherUser)
             ->getJson("/api/chats/{$conversation->id}")
             ->assertForbidden();
    }

    /** @test */
    public function admin_can_access_any_conversation()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $conversation = Conversation::create([
            'contract_type' => 'offer',
            'contract_id' => 1,
            'participant_1' => $this->company->id,
            'participant_2' => $this->expert->id,
            'status' => 'active',
        ]);

        $this->actingAs($admin)
             ->getJson("/api/chats/{$conversation->id}")
             ->assertOk();
    }

    /** @test */
    public function review_updates_expert_ratings()
    {
        $offer = ProjectOffer::create([
            'project_id' => 1,
            'expert_id' => $this->expert->id,
            'price' => 1000,
            'delivery_time_days' => 7,
            'status' => 'accepted',
            'service_status' => 'completed',
            'completed_at' => now(),
        ]);

        $conversation = Conversation::create([
            'contract_type' => 'offer',
            'contract_id' => $offer->id,
            'participant_1' => $this->company->id,
            'participant_2' => $this->expert->id,
            'status' => 'closed',
        ]);

        $this->actingAs($this->company)
             ->postJson("/api/contracts/offer/{$offer->id}/review", [
                 'overall' => 5,
                 'communication' => 5,
                 'quality' => 5,
                 'delivery_time' => 4,
                 'comment' => 'Excellent work!',
             ])
             ->assertOk();

        $this->assertDatabaseHas('reviews', [
            'contract_type' => 'offer',
            'contract_id' => $offer->id,
            'expert_id' => $this->expert->id,
            'company_id' => $this->company->id,
            'rating' => 5,
        ]);

        $this->expert->refresh();
        $this->assertEquals(5, $this->expert->rating_average);
        $this->assertEquals(1, $this->expert->rating_count);
    }

    /** @test */
    public function cannot_submit_duplicate_review()
    {
        $offer = ProjectOffer::create([
            'project_id' => 1,
            'expert_id' => $this->expert->id,
            'price' => 1000,
            'delivery_time_days' => 7,
            'status' => 'accepted',
            'service_status' => 'completed',
            'completed_at' => now(),
        ]);

        $conversation = Conversation::create([
            'contract_type' => 'offer',
            'contract_id' => $offer->id,
            'participant_1' => $this->company->id,
            'participant_2' => $this->expert->id,
            'status' => 'closed',
        ]);

        // First review succeeds
        $this->actingAs($this->company)
             ->postJson("/api/contracts/offer/{$offer->id}/review", [
                 'overall' => 5,
                 'communication' => 5,
                 'quality' => 5,
                 'delivery_time' => 4,
                 'comment' => 'Great!',
             ])
             ->assertOk();

        // Second review fails
        $this->actingAs($this->company)
             ->postJson("/api/contracts/offer/{$offer->id}/review", [
                 'overall' => 4,
                 'communication' => 4,
                 'quality' => 4,
                 'delivery_time' => 4,
                 'comment' => 'Good!',
             ])
             ->assertStatus(500); // Exception thrown
    }
}
