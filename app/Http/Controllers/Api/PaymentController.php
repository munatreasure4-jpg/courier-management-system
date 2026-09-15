<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Shipment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Get all payments
     */
    public function index(Request $request)
    {
        $query = Payment::with('shipment', 'user');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $payments = $query->latest('created_at')->paginate($request->per_page ?? 15);

        return response()->json($payments, 200);
    }

    /**
     * Process payment
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'shipment_id' => 'required|exists:shipments,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:credit_card,debit_card,bank_transfer,cash,wallet',
            'stripe_token' => 'nullable|string',
        ]);

        $shipment = Shipment::find($validated['shipment_id']);

        // Check if payment already exists and is completed
        $existingPayment = Payment::where('shipment_id', $shipment->id)
            ->where('status', Payment::STATUS_COMPLETED)
            ->first();

        if ($existingPayment) {
            return response()->json([
                'message' => 'Payment already completed for this shipment',
            ], 422);
        }

        $payment = Payment::create([
            'shipment_id' => $shipment->id,
            'user_id' => $request->user()->id,
            'status' => Payment::STATUS_PROCESSING,
            ...$validated,
        ]);

        // TODO: Integrate with Stripe API for actual payment processing
        // For now, we'll mark it as completed
        $payment->update([
            'status' => Payment::STATUS_COMPLETED,
            'paid_at' => now(),
            'transaction_id' => 'TXN_' . uniqid(),
        ]);

        return response()->json([
            'message' => 'Payment processed successfully',
            'payment' => $payment,
        ], 201);
    }

    /**
     * Get payment details
     */
    public function show(Payment $payment)
    {
        $payment->load('shipment', 'user');

        return response()->json($payment, 200);
    }

    /**
     * Refund payment
     */
    public function refund(Request $request, Payment $payment)
    {
        if ($payment->status !== Payment::STATUS_COMPLETED) {
            return response()->json([
                'message' => 'Only completed payments can be refunded',
            ], 422);
        }

        $payment->update([
            'status' => Payment::STATUS_REFUNDED,
        ]);

        return response()->json([
            'message' => 'Payment refunded successfully',
            'payment' => $payment,
        ], 200);
    }

    /**
     * Get payment statistics
     */
    public function statistics(Request $request)
    {
        $fromDate = $request->from_date ? strtotime($request->from_date) : strtotime('-30 days');
        $toDate = $request->to_date ? strtotime($request->to_date) : time();

        $totalRevenue = Payment::whereBetween('paid_at', [
            date('Y-m-d', $fromDate),
            date('Y-m-d', $toDate),
        ])->where('status', Payment::STATUS_COMPLETED)->sum('amount');

        $totalTransactions = Payment::whereBetween('paid_at', [
            date('Y-m-d', $fromDate),
            date('Y-m-d', $toDate),
        ])->where('status', Payment::STATUS_COMPLETED)->count();

        $paymentsByMethod = Payment::where('status', Payment::STATUS_COMPLETED)
            ->groupBy('payment_method')
            ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
            ->get();

        return response()->json([
            'total_revenue' => $totalRevenue,
            'total_transactions' => $totalTransactions,
            'average_transaction' => $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0,
            'payments_by_method' => $paymentsByMethod,
        ], 200);
    }
}
