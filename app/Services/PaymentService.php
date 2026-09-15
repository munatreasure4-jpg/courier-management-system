<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Shipment;

class PaymentService
{
    /**
     * Process payment
     */
    public function processPayment(Shipment $shipment, array $data): Payment
    {
        $payment = Payment::create([
            'shipment_id' => $shipment->id,
            'user_id' => $data['user_id'],
            'amount' => $data['amount'] ?? $shipment->shipping_cost,
            'payment_method' => $data['payment_method'] ?? Payment::METHOD_CREDIT_CARD,
            'status' => Payment::STATUS_PROCESSING,
        ]);

        // Process payment through Stripe or other gateway
        // For now, we'll mark as completed
        return $this->completePayment($payment);
    }

    /**
     * Complete payment
     */
    public function completePayment(Payment $payment): Payment
    {
        $payment->update([
            'status' => Payment::STATUS_COMPLETED,
            'paid_at' => now(),
            'transaction_id' => 'TXN_' . uniqid(),
        ]);

        return $payment->refresh();
    }

    /**
     * Refund payment
     */
    public function refundPayment(Payment $payment): Payment
    {
        if ($payment->status !== Payment::STATUS_COMPLETED) {
            throw new \Exception('Only completed payments can be refunded');
        }

        $payment->update(['status' => Payment::STATUS_REFUNDED]);

        return $payment->refresh();
    }

    /**
     * Get payment statistics
     */
    public function getStatistics(string $fromDate, string $toDate): array
    {
        $completedPayments = Payment::where('status', Payment::STATUS_COMPLETED)
            ->whereBetween('paid_at', [$fromDate, $toDate])
            ->get();

        return [
            'total_revenue' => $completedPayments->sum('amount'),
            'total_transactions' => $completedPayments->count(),
            'average_transaction' => $completedPayments->count() > 0 ? $completedPayments->sum('amount') / $completedPayments->count() : 0,
            'by_method' => $completedPayments->groupBy('payment_method')
                ->map(function ($group) {
                    return [
                        'count' => $group->count(),
                        'total' => $group->sum('amount'),
                    ];
                }),
        ];
    }
}
