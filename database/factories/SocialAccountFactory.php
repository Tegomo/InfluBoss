<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SocialAccount>
 */
class SocialAccountFactory extends Factory
{
    public function definition(): array
    {
        $handle = fake()->unique()->userName();

        return [
            'user_id' => User::factory()->influencer(),
            'platform' => 'instagram',
            'url' => 'https://www.instagram.com/'.$handle,
            'handle' => $handle,
            'followers_count' => null,
            'last_synced_at' => null,
        ];
    }
}
