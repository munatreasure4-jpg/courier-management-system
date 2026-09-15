<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\GPSTracking;
use App\Models\Shipment;

class DeliveryService
{
    /**
     * Assign delivery to personnel
     */
    public function assignDelivery(Shipment $shipment, int $deliveryPersonnelId, array $data = []): Delivery
    {
        $delivery = Delivery::create([
            'shipment_id' => $shipment->id,
            'delivery_personnel_id' => $deliveryPersonnelId,
            'status' => Delivery::STATUS_ASSIGNED,
            'estimated_delivery_time' => $data['estimated_delivery_time'] ?? now()->addHours(24),
        ]);

        // Update shipment status
        $shipment->update(['status' => Shipment::STATUS_PICKED]);

        return $delivery;
    }

    /**
     * Update delivery status
     */
    public function updateDeliveryStatus(Delivery $delivery, string $status, array $data = []): Delivery
    {
        $delivery->update([
            'status' => $status,
            'current_latitude' => $data['latitude'] ?? $delivery->current_latitude,
            'current_longitude' => $data['longitude'] ?? $delivery->current_longitude,
            ...$data,
        ]);

        // Update shipment status based on delivery status
        $this->updateShipmentStatus($delivery->shipment, $status);

        return $delivery->refresh();
    }

    /**
     * Track delivery location
     */
    public function trackLocation(Delivery $delivery, array $gpsData): GPSTracking
    {
        $gpsTracking = $delivery->gpsLocations()->create([
            'timestamp' => now(),
            ...$gpsData,
        ]);

        // Update delivery current location
        $delivery->update([
            'current_latitude' => $gpsData['latitude'],
            'current_longitude' => $gpsData['longitude'],
        ]);

        return $gpsTracking;
    }

    /**
     * Get current location
     */
    public function getCurrentLocation(Delivery $delivery): ?GPSTracking
    {
        return $delivery->gpsLocations()->latest('timestamp')->first();
    }

    /**
     * Mark as delivered
     */
    public function markAsDelivered(Delivery $delivery, array $data = []): Delivery
    {
        return $this->updateDeliveryStatus($delivery, Delivery::STATUS_DELIVERED, array_merge([
            'actual_delivery_time' => now(),
        ], $data));
    }

    /**
     * Mark delivery as failed
     */
    public function markAsFailed(Delivery $delivery, string $reason = ''): Delivery
    {
        return $this->updateDeliveryStatus($delivery, Delivery::STATUS_FAILED, [
            'delivery_notes' => $reason,
        ]);
    }

    /**
     * Update shipment status based on delivery status
     */
    private function updateShipmentStatus(Shipment $shipment, string $deliveryStatus): void
    {
        $statusMap = [
            Delivery::STATUS_PICKED_UP => Shipment::STATUS_IN_TRANSIT,
            Delivery::STATUS_IN_TRANSIT => Shipment::STATUS_IN_TRANSIT,
            Delivery::STATUS_ARRIVING => Shipment::STATUS_OUT_FOR_DELIVERY,
            Delivery::STATUS_DELIVERED => Shipment::STATUS_DELIVERED,
            Delivery::STATUS_FAILED => Shipment::STATUS_CANCELLED,
        ];

        if (isset($statusMap[$deliveryStatus])) {
            $shipment->update(['status' => $statusMap[$deliveryStatus]]);
        }
    }
}
