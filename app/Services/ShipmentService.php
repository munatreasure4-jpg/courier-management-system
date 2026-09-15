<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\TrackingUpdate;
use Illuminate\Pagination\Paginator;

class ShipmentService
{
    /**
     * Create a new shipment with automatic tracking
     */
    public function createShipment(array $data): Shipment
    {
        $data['tracking_number'] = Shipment::generateTrackingNumber();
        $data['status'] = Shipment::STATUS_PENDING;

        $shipment = Shipment::create($data);

        // Create initial tracking record
        $this->createTrackingUpdate($shipment->id, [
            'status' => Shipment::STATUS_PENDING,
            'location' => $data['origin_address'] ?? 'Warehouse',
            'city' => $data['origin_city'] ?? '',
            'state' => $data['origin_state'] ?? '',
            'country' => 'US',
            'description' => 'Shipment created and waiting to be picked up',
        ]);

        return $shipment;
    }

    /**
     * Update shipment status and create tracking record
     */
    public function updateShipmentStatus(Shipment $shipment, string $status, array $trackingData = []): Shipment
    {
        $shipment->update(['status' => $status]);

        $this->createTrackingUpdate($shipment->id, array_merge([
            'status' => $status,
            'location' => $trackingData['location'] ?? '',
            'city' => $trackingData['city'] ?? '',
            'state' => $trackingData['state'] ?? '',
            'country' => $trackingData['country'] ?? 'US',
            'description' => $this->getStatusDescription($status),
        ], $trackingData));

        return $shipment->refresh();
    }

    /**
     * Create tracking update record
     */
    public function createTrackingUpdate(int $shipmentId, array $data): TrackingUpdate
    {
        return TrackingUpdate::create([
            'shipment_id' => $shipmentId,
            'timestamp' => now(),
            ...$data,
        ]);
    }

    /**
     * Get shipment tracking history
     */
    public function getTrackingHistory(Shipment $shipment): Paginator
    {
        return $shipment->tracking()->latest('timestamp')->paginate(25);
    }

    /**
     * Get status description
     */
    private function getStatusDescription(string $status): string
    {
        $descriptions = [
            Shipment::STATUS_PENDING => 'Shipment is waiting to be picked up',
            Shipment::STATUS_PICKED => 'Shipment has been picked up',
            Shipment::STATUS_IN_TRANSIT => 'Shipment is in transit to destination',
            Shipment::STATUS_OUT_FOR_DELIVERY => 'Shipment is out for delivery today',
            Shipment::STATUS_DELIVERED => 'Shipment has been delivered',
            Shipment::STATUS_CANCELLED => 'Shipment has been cancelled',
            Shipment::STATUS_RETURNED => 'Shipment has been returned',
        ];

        return $descriptions[$status] ?? 'Status updated';
    }

    /**
     * Calculate shipping cost
     */
    public function calculateShippingCost(array $data): float
    {
        $baseCost = 5.00;
        $weightCost = ($data['weight'] ?? 0) * 0.5;
        $distanceCost = $this->calculateDistance($data) * 0.01;

        return round($baseCost + $weightCost + $distanceCost, 2);
    }

    /**
     * Calculate distance between origin and destination (simplified)
     */
    private function calculateDistance(array $data): float
    {
        // This is a simplified version
        // In production, use Google Maps Distance Matrix API
        return rand(50, 500);
    }
}
