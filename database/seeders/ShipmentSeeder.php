<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Shipment;
use App\Models\User;

class ShipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = User::where('role', User::ROLE_CUSTOMER)->get();
        $statuses = [
            Shipment::STATUS_PENDING,
            Shipment::STATUS_PICKED,
            Shipment::STATUS_IN_TRANSIT,
            Shipment::STATUS_OUT_FOR_DELIVERY,
            Shipment::STATUS_DELIVERED,
        ];

        foreach ($customers as $customer) {
            for ($i = 0; $i < 3; $i++) {
                Shipment::create([
                    'tracking_number' => 'TRK' . strtoupper(uniqid()) . rand(1000, 9999),
                    'sender_id' => $customer->id,
                    'receiver_name' => "Recipient {$i}",
                    'receiver_email' => "recipient{$i}@example.com",
                    'receiver_phone' => '+1' . rand(2000000000, 9999999999),
                    'origin_address' => $customer->address,
                    'origin_city' => $customer->city,
                    'origin_state' => $customer->state,
                    'origin_postal_code' => $customer->postal_code,
                    'destination_address' => rand(100, 9999) . ' Destination Ave',
                    'destination_city' => ['New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix'][rand(0, 4)],
                    'destination_state' => ['NY', 'CA', 'IL', 'TX', 'AZ'][rand(0, 4)],
                    'destination_postal_code' => rand(10000, 99999),
                    'weight' => rand(1, 50),
                    'dimensions' => rand(5, 20) . 'x' . rand(5, 20) . 'x' . rand(5, 20),
                    'contents_description' => 'Package containing ' . ['Electronics', 'Books', 'Clothing', 'Food', 'Documents'][rand(0, 4)],
                    'status' => $statuses[rand(0, count($statuses) - 1)],
                    'shipping_cost' => rand(500, 5000) / 100,
                    'pickup_date' => now()->subDays(rand(0, 10)),
                    'delivery_date' => now()->addDays(rand(1, 5)),
                ]);
            }
        }
    }
}
