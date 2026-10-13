<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contract>
 */
class ContractFactory extends Factory
{
    public function definition(): array
    {
        return [
            'influencer_id' => User::factory()->influencer(),
            'type' => 'ambassador',
            'start_date' => now()->subMonths(2)->toDateString(),
            'end_date' => now()->addMonths(4)->toDateString(),
            'amount' => 500000,
            'payment_terms' => '50 % à la signature, 50 % à la fin du contrat',
            'status' => 'active',
        ];
    }
}
