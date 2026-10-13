<?php

namespace Database\Factories;

use App\Models\Publication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Stat>
 */
class StatFactory extends Factory
{
    public function definition(): array
    {
        return [
            'publication_id' => Publication::factory(),
            'collected_at' => now(),
            'likes' => 0,
            'comments' => 0,
            'shares' => 0,
            'saves' => 0,
            'views' => 0,
        ];
    }
}
