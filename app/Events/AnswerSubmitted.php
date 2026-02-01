<?php

namespace App\Events;

use App\Models\AiResponse;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AnswerSubmitted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $response;

    /**
     * Create a new event instance.
     */
    public function __construct(AiResponse $response)
    {
        $this->response = $response;
    }
}
