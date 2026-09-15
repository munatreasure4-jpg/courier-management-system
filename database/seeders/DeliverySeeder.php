<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Delivery;
use App\Models\Shipment;
use App\Models\User;

class DeliverySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $deliveryPersonnel = User::where('role', User::ROLE_DELIVERY)->get();
        $shipments = Shipment::whereIn('status', [
            Shipment::STATUS_PICKED,
            Shipment::STATUS_IN_TRANSIT,
            Shipment::STATUS_OUT_FOR_DELIVERY,
            Shipment::STATUS_DELIVERED,
        ])->get();

        foreach ($shipments as $shipment) {
            Delivery::create([
                'shipment_id' => $shipment->id,
                'delivery_personnel_id' => $deliveryPersonnel->random()->id,
                'current_latitude' => rand(-90000000, 90000000) / 1000000,
                'current_longitude' => rand(-180000000, 180000000) / 1000000,
                'status' => Delivery::STATUS_IN_TRANSIT,
                'estimated_delivery_time' => now()->addHours(rand(1, 24)),
                'actual_delivery_time' => $shipment->status === Shipment::STATUS_DELIVERED ? now() : null,
            ]);
        }
    }
}
