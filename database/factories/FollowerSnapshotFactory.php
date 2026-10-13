<?php

namespace Database\Factories;

use App\Models\SocialAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FollowerSnapshot>
 */
class FollowerSnapshotFactory extends Factory
{
    public function definition(): array
    {
        return [
            'social_account_id' => SocialAccount::factory(),
            'followers_count' => 1000,
            'collected_at' => now(),
            'source' => 'manual',
            'recorded_by' => null,
        ];
    }
}
