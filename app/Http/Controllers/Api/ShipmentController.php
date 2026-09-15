<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\TrackingUpdate;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    /**
     * Get all shipments (with pagination)
     */
    public function index(Request $request)
    {
        $query = Shipment::with('sender', 'delivery', 'payment');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Search by tracking number or receiver
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('tracking_number', 'like', "%{$search}%")
                    ->orWhere('receiver_name', 'like', "%{$search}%")
                    ->orWhere('receiver_email', 'like', "%{$search}%");
            });
        }

        $shipments = $query->latest('created_at')->paginate($request->per_page ?? 15);

        return response()->json($shipments, 200);
    }

    /**
     * Create a new shipment
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'receiver_name' => 'required|string|max:255',
            'receiver_email' => 'required|email',
            'receiver_phone' => 'required|string|max:20',
            'origin_address' => 'required|string',
            'origin_city' => 'required|string',
            'origin_state' => 'required|string',
            'origin_postal_code' => 'required|string',
            'destination_address' => 'required|string',
            'destination_city' => 'required|string',
            'destination_state' => 'required|string',
            'destination_postal_code' => 'required|string',
            'weight' => 'required|numeric|min:0.1',
            'dimensions' => 'nullable|string',
            'contents_description' => 'required|string',
            'shipping_cost' => 'required|numeric|min:0',
        ]);

        $shipment = Shipment::create([
            'tracking_number' => Shipment::generateTrackingNumber(),
            'sender_id' => $request->user()->id,
            'status' => Shipment::STATUS_PENDING,
            ...$validated,
        ]);

        return response()->json([
            'message' => 'Shipment created successfully',
            'shipment' => $shipment,
        ], 201);
    }

    /**
     * Get shipment details
     */
    public function show(Shipment $shipment)
    {
        $shipment->load('sender', 'delivery', 'payment', 'tracking');

        return response()->json($shipment, 200);
    }

    /**
     * Update shipment
     */
    public function update(Request $request, Shipment $shipment)
    {
        $this->authorize('update', $shipment);

        $validated = $request->validate([
            'receiver_name' => 'sometimes|string|max:255',
            'receiver_email' => 'sometimes|email',
            'receiver_phone' => 'sometimes|string|max:20',
            'status' => 'sometimes|in:pending,picked,in_transit,out_for_delivery,delivered,cancelled,returned',
            'notes' => 'sometimes|string',
        ]);

        $shipment->update($validated);

        return response()->json([
            'message' => 'Shipment updated successfully',
            'shipment' => $shipment,
        ], 200);
    }

    /**
     * Delete shipment
     */
    public function destroy(Shipment $shipment)
    {
        $this->authorize('delete', $shipment);

        $shipment->delete();

        return response()->json([
            'message' => 'Shipment deleted successfully',
        ], 200);
    }

    /**
     * Track shipment
     */
    public function track($trackingNumber)
    {
        $shipment = Shipment::where('tracking_number', $trackingNumber)
            ->with('tracking')
            ->firstOrFail();

        return response()->json([
            'shipment' => $shipment,
            'tracking_updates' => $shipment->tracking()->latest('timestamp')->get(),
        ], 200);
    }

    /**
     * Add tracking update
     */
    public function addTrackingUpdate(Request $request, Shipment $shipment)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,picked,in_transit,out_for_delivery,delivered,cancelled,returned',
            'location' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'country' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'description' => 'nullable|string',
        ]);

        $trackingUpdate = $shipment->tracking()->create([
            'timestamp' => now(),
            ...$validated,
        ]);

        // Update shipment status
        $shipment->update(['status' => $validated['status']]);

        return response()->json([
            'message' => 'Tracking update added successfully',
            'tracking_update' => $trackingUpdate,
        ], 201);
    }
}
