<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'influencer_id' => User::factory()->influencer(),
            'label' => 'Acompte',
            'amount' => 250000,
            'due_date' => now()->addWeeks(2)->toDateString(),
            'status' => 'pending',
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => 'paid',
            'paid_at' => now()->subDay()->toDateString(),
            'method' => 'bank_transfer',
        ]);
    }
}
