<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function statistics(Request $request)
    {
        $user = $request->user();
        $fromDate = $request->from_date ?? now()->subDays(30)->toDateString();
        $toDate = $request->to_date ?? now()->toDateString();

        // Admin statistics
        if ($user->role === User::ROLE_ADMIN) {
            $stats = [
                'total_users' => User::count(),
                'total_customers' => User::where('role', User::ROLE_CUSTOMER)->count(),
                'total_delivery_personnel' => User::where('role', User::ROLE_DELIVERY)->count(),
                'total_shipments' => Shipment::count(),
                'active_shipments' => Shipment::active()->count(),
                'delivered_shipments' => Shipment::where('status', Shipment::STATUS_DELIVERED)
                    ->whereBetween('delivery_date', [$fromDate, $toDate])
                    ->count(),
                'total_revenue' => Payment::where('status', Payment::STATUS_COMPLETED)
                    ->whereBetween('paid_at', [$fromDate, $toDate])
                    ->sum('amount'),
                'pending_payments' => Payment::where('status', Payment::STATUS_PENDING)->count(),
            ];
        }
        // Delivery personnel statistics
        else if ($user->role === User::ROLE_DELIVERY) {
            $stats = [
                'total_assignments' => Delivery::where('delivery_personnel_id', $user->id)->count(),
                'completed_deliveries' => Delivery::where('delivery_personnel_id', $user->id)
                    ->where('status', Delivery::STATUS_DELIVERED)
                    ->whereBetween('actual_delivery_time', [$fromDate, $toDate])
                    ->count(),
                'pending_deliveries' => Delivery::where('delivery_personnel_id', $user->id)
                    ->whereIn('status', [Delivery::STATUS_ASSIGNED, Delivery::STATUS_IN_TRANSIT])
                    ->count(),
                'failed_deliveries' => Delivery::where('delivery_personnel_id', $user->id)
                    ->where('status', Delivery::STATUS_FAILED)
                    ->count(),
            ];
        }
        // Customer statistics
        else {
            $stats = [
                'total_shipments' => Shipment::where('sender_id', $user->id)->count(),
                'active_shipments' => Shipment::where('sender_id', $user->id)->active()->count(),
                'delivered_shipments' => Shipment::where('sender_id', $user->id)
                    ->where('status', Shipment::STATUS_DELIVERED)
                    ->whereBetween('delivery_date', [$fromDate, $toDate])
                    ->count(),
                'total_spent' => Payment::where('user_id', $user->id)
                    ->where('status', Payment::STATUS_COMPLETED)
                    ->whereBetween('paid_at', [$fromDate, $toDate])
                    ->sum('amount'),
            ];
        }

        return response()->json($stats, 200);
    }

    /**
     * Get dashboard chart data
     */
    public function charts(Request $request)
    {
        $user = $request->user();
        $days = $request->days ?? 30;
        $fromDate = now()->subDays($days)->toDateString();

        // Shipment status distribution
        $shipmentsByStatus = Shipment::whereBetween('created_at', [$fromDate, now()])
            ->groupBy('status')
            ->selectRaw('status, COUNT(*) as count')
            ->get();

        // Daily revenue
        $dailyRevenue = Payment::where('status', Payment::STATUS_COMPLETED)
            ->whereBetween('paid_at', [$fromDate, now()])
            ->groupBy('paid_at')
            ->selectRaw('DATE(paid_at) as date, SUM(amount) as total')
            ->orderBy('date')
            ->get();

        // Payment methods distribution
        $paymentsByMethod = Payment::where('status', Payment::STATUS_COMPLETED)
            ->groupBy('payment_method')
            ->selectRaw('payment_method, COUNT(*) as count')
            ->get();

        return response()->json([
            'shipments_by_status' => $shipmentsByStatus,
            'daily_revenue' => $dailyRevenue,
            'payments_by_method' => $paymentsByMethod,
        ], 200);
    }
}
