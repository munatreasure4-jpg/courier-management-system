<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\GPSTracking;
use App\Models\Shipment;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    /**
     * Get all deliveries
     */
    public function index(Request $request)
    {
        $query = Delivery::with('shipment', 'deliveryPersonnel', 'gpsLocations');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by delivery personnel
        if ($request->has('delivery_personnel_id')) {
            $query->where('delivery_personnel_id', $request->delivery_personnel_id);
        }

        $deliveries = $query->latest('created_at')->paginate($request->per_page ?? 15);

        return response()->json($deliveries, 200);
    }

    /**
     * Assign delivery to personnel
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'shipment_id' => 'required|exists:shipments,id',
            'delivery_personnel_id' => 'required|exists:users,id',
            'estimated_delivery_time' => 'nullable|date',
        ]);

        // Check if shipment is already assigned
        if (Delivery::where('shipment_id', $validated['shipment_id'])->exists()) {
            return response()->json([
                'message' => 'Shipment already has a delivery assignment',
            ], 422);
        }

        $delivery = Delivery::create([
            'status' => Delivery::STATUS_ASSIGNED,
            ...$validated,
        ]);

        // Update shipment status
        Shipment::find($validated['shipment_id'])->update(['status' => Shipment::STATUS_PICKED]);

        return response()->json([
            'message' => 'Delivery assigned successfully',
            'delivery' => $delivery,
        ], 201);
    }

    /**
     * Get delivery details
     */
    public function show(Delivery $delivery)
    {
        $delivery->load('shipment', 'deliveryPersonnel', 'gpsLocations');

        return response()->json($delivery, 200);
    }

    /**
     * Update delivery status
     */
    public function updateStatus(Request $request, Delivery $delivery)
    {
        $validated = $request->validate([
            'status' => 'required|in:assigned,picked_up,in_transit,arriving,delivered,failed',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'delivery_notes' => 'nullable|string',
            'signature_url' => 'nullable|url',
            'photo_url' => 'nullable|url',
            'recipient_name' => 'nullable|string',
        ]);

        $delivery->update($validated);

        // Update shipment status based on delivery status
        $statusMap = [
            Delivery::STATUS_PICKED_UP => Shipment::STATUS_IN_TRANSIT,
            Delivery::STATUS_IN_TRANSIT => Shipment::STATUS_IN_TRANSIT,
            Delivery::STATUS_ARRIVING => Shipment::STATUS_OUT_FOR_DELIVERY,
            Delivery::STATUS_DELIVERED => Shipment::STATUS_DELIVERED,
        ];

        if (isset($statusMap[$validated['status']])) {
            $delivery->shipment->update(['status' => $statusMap[$validated['status']]]);
        }

        return response()->json([
            'message' => 'Delivery status updated successfully',
            'delivery' => $delivery,
        ], 200);
    }

    /**
     * Get current location of delivery
     */
    public function getCurrentLocation(Delivery $delivery)
    {
        $location = $delivery->gpsLocations()->latest('timestamp')->first();

        return response()->json([
            'delivery_id' => $delivery->id,
            'current_location' => $location,
            'status' => $delivery->status,
        ], 200);
    }

    /**
     * Add GPS location tracking
     */
    public function trackLocation(Request $request, Delivery $delivery)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'accuracy' => 'nullable|numeric',
            'speed' => 'nullable|numeric',
            'heading' => 'nullable|numeric',
            'altitude' => 'nullable|numeric',
        ]);

        $gpsTracking = $delivery->gpsLocations()->create([
            'timestamp' => now(),
            ...$validated,
        ]);

        // Update delivery current location
        $delivery->update([
            'current_latitude' => $validated['latitude'],
            'current_longitude' => $validated['longitude'],
        ]);

        return response()->json([
            'message' => 'Location tracked successfully',
            'gps_tracking' => $gpsTracking,
        ], 201);
    }

    /**
     * Get GPS tracking history
     */
    public function getTrackingHistory(Delivery $delivery)
    {
        $history = $delivery->gpsLocations()->latest('timestamp')->paginate(50);

        return response()->json($history, 200);
    }
}
