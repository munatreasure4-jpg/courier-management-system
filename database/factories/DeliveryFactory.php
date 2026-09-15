<?php

namespace Database\Factories;

use App\Models\Delivery;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Delivery>
 */
class DeliveryFactory extends Factory
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
            'delivery_personnel_id' => User::factory()->deliveryPersonnel(),
            'current_latitude' => fake()->latitude(),
            'current_longitude' => fake()->longitude(),
            'status' => 'assigned',
            'estimated_delivery_time' => now()->addHours(random_int(1, 24)),
        ];
    }

    /**
     * Create a delivered delivery
     */
    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Delivery::STATUS_DELIVERED,
            'actual_delivery_time' => now(),
        ]);
    }
}
