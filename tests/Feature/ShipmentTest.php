<?php

namespace Tests\Feature;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShipmentTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test get all shipments
     */
    public function test_can_get_all_shipments(): void
    {
        Shipment::factory()->count(5)->create(['sender_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/shipments');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta']);
    }

    /**
     * Test create shipment
     */
    public function test_can_create_shipment(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/shipments', [
                'receiver_name' => 'John Doe',
                'receiver_email' => 'john@example.com',
                'receiver_phone' => '+1234567890',
                'origin_address' => '123 Main St',
                'origin_city' => 'New York',
                'origin_state' => 'NY',
                'origin_postal_code' => '10001',
                'destination_address' => '456 Oak Ave',
                'destination_city' => 'Los Angeles',
                'destination_state' => 'CA',
                'destination_postal_code' => '90001',
                'weight' => 5.5,
                'contents_description' => 'Electronics',
                'shipping_cost' => 25.00,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'shipment']);

        $this->assertDatabaseHas('shipments', [
            'sender_id' => $this->user->id,
            'receiver_email' => 'john@example.com',
        ]);
    }

    /**
     * Test get shipment details
     */
    public function test_can_get_shipment_details(): void
    {
        $shipment = Shipment::factory()->create(['sender_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/shipments/{$shipment->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['id', 'tracking_number', 'status']);
    }

    /**
     * Test track shipment
     */
    public function test_can_track_shipment(): void
    {
        $shipment = Shipment::factory()->create();

        $response = $this->getJson("/api/shipments/track/{$shipment->tracking_number}");

        $response->assertStatus(200)
            ->assertJsonStructure(['shipment', 'tracking_updates']);
    }
}
