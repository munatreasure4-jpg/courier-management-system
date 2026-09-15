<?php

namespace Database\Factories;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shipment>
 */
class ShipmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tracking_number' => 'TRK' . strtoupper(fake()->unique()->bothify('???###')) . rand(1000, 9999),
            'sender_id' => User::factory(),
            'receiver_name' => fake()->name(),
            'receiver_email' => fake()->email(),
            'receiver_phone' => fake()->phoneNumber(),
            'origin_address' => fake()->streetAddress(),
            'origin_city' => fake()->city(),
            'origin_state' => fake()->state(),
            'origin_postal_code' => fake()->postcode(),
            'destination_address' => fake()->streetAddress(),
            'destination_city' => fake()->city(),
            'destination_state' => fake()->state(),
            'destination_postal_code' => fake()->postcode(),
            'weight' => fake()->randomFloat(2, 0.5, 50),
            'dimensions' => fake()->bothify('##x##x##'),
            'contents_description' => fake()->sentence(),
            'status' => 'pending',
            'shipping_cost' => fake()->randomFloat(2, 5, 100),
            'pickup_date' => now(),
            'delivery_date' => now()->addDays(random_int(1, 7)),
        ];
    }

    /**
     * Create a delivered shipment
     */
    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Shipment::STATUS_DELIVERED,
            'delivery_date' => now()->subDays(random_int(1, 7)),
        ]);
    }

    /**
     * Create a pending shipment
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Shipment::STATUS_PENDING,
        ]);
    }

    /**
     * Create an in-transit shipment
     */
    public function inTransit(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Shipment::STATUS_IN_TRANSIT,
        ]);
    }
}
