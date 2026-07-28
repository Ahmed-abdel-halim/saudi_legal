<?php

namespace Tests\Feature;

use App\Models\WhatsAppConversation;
use App\Models\LegalJudgment;
use App\Services\TwilioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppCaseLookupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $twilioMock = \Mockery::mock(TwilioService::class);
        $twilioMock->shouldReceive('sendMessage')->byDefault()->andReturnTrue();
        $this->app->instance(TwilioService::class, $twilioMock);
    }

    /** @test */
    public function it_handles_case_lookup_with_judgment_number_and_returns_wa_me_link()
    {
        LegalJudgment::create([
            'case_number'   => '4430630992',
            'title'         => 'حكم قضائي تجاري',
            'summary'       => 'أسباب وموجز الحكم',
            'judgment_text' => 'نص الحكم الكامل للبدء',
            'case_text'     => 'نص القضية والوقائع',
        ]);

        $response = $this->postJson('/api/whatsapp/webhook', [
            'From' => 'whatsapp:+966500000000',
            'To'   => 'whatsapp:+966570079182',
            'Body' => '4430630992',
        ]);

        $response->assertStatus(200);

        $conversation = WhatsAppConversation::where('phone_number', 'whatsapp:+966500000000')->first();
        $this->assertNotNull($conversation);
    }
}
