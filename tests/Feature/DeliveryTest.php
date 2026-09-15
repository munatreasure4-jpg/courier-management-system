<?php

namespace Tests\Feature;

use App\Models\Delivery;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $deliveryPersonnel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->deliveryPersonnel = User::factory()->create(['role' => User::ROLE_DELIVERY]);
    }

    /**
     * Test assign delivery
     */
    public function test_can_assign_delivery(): void
    {
        $shipment = Shipment::factory()->create();

        $response = $this->actingAs($this->admin)
            ->postJson('/api/deliveries', [
                'shipment_id' => $shipment->id,
                'delivery_personnel_id' => $this->deliveryPersonnel->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'delivery']);

        $this->assertDatabaseHas('deliveries', [
            'shipment_id' => $shipment->id,
            'delivery_personnel_id' => $this->deliveryPersonnel->id,
        ]);
    }

    /**
     * Test update delivery status
     */
    public function test_can_update_delivery_status(): void
    {
        $delivery = Delivery::factory()->create([
            'delivery_personnel_id' => $this->deliveryPersonnel->id,
        ]);

        $response = $this->actingAs($this->deliveryPersonnel)
            ->putJson("/api/deliveries/{$delivery->id}/status", [
                'status' => Delivery::STATUS_DELIVERED,
                'latitude' => 40.7128,
                'longitude' => -74.0060,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'delivery']);
    }

    /**
     * Test track location
     */
    public function test_can_track_location(): void
    {
        $delivery = Delivery::factory()->create([
            'delivery_personnel_id' => $this->deliveryPersonnel->id,
        ]);

        $response = $this->actingAs($this->deliveryPersonnel)
            ->postJson("/api/deliveries/{$delivery->id}/track-location", [
                'latitude' => 40.7128,
                'longitude' => -74.0060,
                'accuracy' => 5.0,
                'speed' => 10.5,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'gps_tracking']);
    }
}
