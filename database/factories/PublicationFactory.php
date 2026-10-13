<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Publication>
 */
class PublicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'assignment_id' => Assignment::factory(),
            'influencer_id' => User::factory()->influencer(),
            'platform' => 'instagram',
            'post_url' => 'https://www.instagram.com/p/'.fake()->unique()->lexify('???????????'),
            'post_type' => 'post',
            'published_at' => now()->subDays(3),
            'status' => 'verified',
        ];
    }

    public function pendingVerification(): static
    {
        return $this->state(fn () => ['status' => 'pending_verification']);
    }
}
