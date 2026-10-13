<?php

namespace Database\Factories;

use App\Models\Assignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Deliverable>
 */
class DeliverableFactory extends Factory
{
    public function definition(): array
    {
        return [
            'assignment_id' => Assignment::factory(),
            'type' => 'post',
            'platform' => 'instagram',
            'quantity' => 1,
            'due_date' => now()->addWeek()->toDateString(),
            'status' => 'todo',
        ];
    }
}
