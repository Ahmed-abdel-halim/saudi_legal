<?php

namespace Database\Factories;

use App\Models\AiTask;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AiTask>
 */
class AiTaskFactory extends Factory
{
    protected $model = AiTask::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'task_type' => 'linguistic_review',
            'original_data' => $this->faker->sentence(),
            'ai_suggestion' => $this->faker->sentence(),
            'status' => 'pending',
            'assigned_expert_id' => null,
            'assigned_at' => null,
            'completed_at' => null,
            'is_gold_standard' => false,
            'gold_answer' => null,
            'required_responses' => 1,
            'current_responses' => 0,
            'consensus_status' => 'pending',
        ];
    }
}
