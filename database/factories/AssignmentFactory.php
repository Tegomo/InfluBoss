<?php

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Assignment>
 */
class AssignmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'influencer_id' => User::factory()->influencer(),
            'status' => 'accepted',
            'required_posts' => 2,
            'deadline' => now()->addWeeks(2)->toDateString(),
        ];
    }
}
