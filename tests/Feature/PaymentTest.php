<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test get all payments
     */
    public function test_can_get_all_payments(): void
    {
        Payment::factory()->count(5)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/payments');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta']);
    }

    /**
     * Test process payment
     */
    public function test_can_process_payment(): void
    {
        $shipment = Shipment::factory()->create(['sender_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/payments', [
                'shipment_id' => $shipment->id,
                'amount' => $shipment->shipping_cost,
                'payment_method' => 'credit_card',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'payment']);
    }

    /**
     * Test refund payment
     */
    public function test_can_refund_payment(): void
    {
        $payment = Payment::factory()->create([
            'user_id' => $this->user->id,
            'status' => Payment::STATUS_COMPLETED,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/payments/{$payment->id}/refund");

        $response->assertStatus(200);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => Payment::STATUS_REFUNDED,
        ]);
    }
}
