<?php

namespace Tests\Feature;

use App\Models\WhatsAppConversation;
use App\Services\TwilioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppGreetingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock TwilioService
        $twilioMock = \Mockery::mock(TwilioService::class);
        $twilioMock->shouldReceive('sendMessage')->byDefault()->andReturnTrue();
        $twilioMock->shouldReceive('sendTypingIndicator')->byDefault()->andReturnTrue();
        $this->app->instance(TwilioService::class, $twilioMock);
    }

    /** @test */
    public function it_replies_to_pure_greeting_without_disclaimer_or_citations()
    {
        $response = $this->postJson('/api/whatsapp/webhook', [
            'From' => 'whatsapp:+966500000000',
            'Body' => 'المساعد القانوني',
        ]);

        $response->assertStatus(200);

        // Verify conversation state was updated to in_chat
        $conversation = WhatsAppConversation::where('phone_number', 'whatsapp:+966500000000')->first();
        $this->assertNotNull($conversation);
        $this->assertEquals('in_chat', $conversation->session_state);
    }

    /** @test */
    public function it_replies_to_greeting_in_active_session_without_rag_disclaimer()
    {
        // First create active conversation
        $conversation = WhatsAppConversation::create([
            'phone_number' => 'whatsapp:+966511111111',
            'session_state' => 'in_chat',
            'message_count' => 1,
            'free_limit' => 10,
        ]);

        $response = $this->postJson('/api/whatsapp/webhook', [
            'From' => 'whatsapp:+966511111111',
            'Body' => 'صباح الخير',
        ]);

        $response->assertStatus(200);

        // Verify conversation is still in_chat
        $this->assertEquals('in_chat', $conversation->fresh()->session_state);
    }
}
