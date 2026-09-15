<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\Shipment;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shipments = Shipment::all();
        $methods = [
            Payment::METHOD_CREDIT_CARD,
            Payment::METHOD_DEBIT_CARD,
            Payment::METHOD_BANK_TRANSFER,
            Payment::METHOD_WALLET,
        ];

        foreach ($shipments->random(15) as $shipment) {
            Payment::create([
                'shipment_id' => $shipment->id,
                'user_id' => $shipment->sender_id,
                'amount' => $shipment->shipping_cost,
                'payment_method' => $methods[rand(0, count($methods) - 1)],
                'status' => [Payment::STATUS_PENDING, Payment::STATUS_COMPLETED][rand(0, 1)],
                'transaction_id' => 'TXN_' . uniqid(),
                'paid_at' => rand(0, 1) ? now()->subDays(rand(1, 5)) : null,
            ]);
        }
    }
}
