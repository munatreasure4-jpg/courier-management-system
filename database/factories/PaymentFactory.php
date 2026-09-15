<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shipment_id' => Shipment::factory(),
            'user_id' => User::factory(),
            'amount' => fake()->randomFloat(2, 5, 100),
            'payment_method' => fake()->randomElement(['credit_card', 'debit_card', 'bank_transfer', 'wallet']),
            'status' => 'pending',
            'transaction_id' => 'TXN_' . uniqid(),
        ];
    }

    /**
     * Create a completed payment
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Payment::STATUS_COMPLETED,
            'paid_at' => now(),
        ]);
    }
}
