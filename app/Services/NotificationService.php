<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Shipment;

class NotificationService
{
    /**
     * Send notification to user
     */
    public function sendNotification(
        User $user,
        string $type,
        string $subject,
        string $message,
        ?Shipment $shipment = null,
        string $channel = Notification::CHANNEL_IN_APP
    ): Notification
    {
        $notification = Notification::create([
            'user_id' => $user->id,
            'shipment_id' => $shipment?->id,
            'type' => $type,
            'subject' => $subject,
            'message' => $message,
            'channel' => $channel,
        ]);

        // Send through different channels
        if ($channel === Notification::CHANNEL_EMAIL) {
            $this->sendEmail($user, $subject, $message);
        } elseif ($channel === Notification::CHANNEL_SMS) {
            $this->sendSMS($user, $message);
        }

        return $notification;
    }

    /**
     * Send shipment notification
     */
    public function notifyShipmentStatus(Shipment $shipment, string $status): void
    {
        $messages = [
            Shipment::STATUS_PENDING => [
                'subject' => 'Shipment Created',
                'message' => "Your shipment {$shipment->tracking_number} has been created and is waiting to be picked up.",
            ],
            Shipment::STATUS_PICKED => [
                'subject' => 'Shipment Picked Up',
                'message' => "Your shipment {$shipment->tracking_number} has been picked up.",
            ],
            Shipment::STATUS_IN_TRANSIT => [
                'subject' => 'Shipment In Transit',
                'message' => "Your shipment {$shipment->tracking_number} is now in transit to {$shipment->destination_city}.",
            ],
            Shipment::STATUS_OUT_FOR_DELIVERY => [
                'subject' => 'Out for Delivery',
                'message' => "Your shipment {$shipment->tracking_number} is out for delivery today.",
            ],
            Shipment::STATUS_DELIVERED => [
                'subject' => 'Delivered',
                'message' => "Your shipment {$shipment->tracking_number} has been delivered.",
            ],
        ];

        if (isset($messages[$status])) {
            $sender = $shipment->sender;
            $this->sendNotification(
                $sender,
                'shipment_status_updated',
                $messages[$status]['subject'],
                $messages[$status]['message'],
                $shipment,
                Notification::CHANNEL_EMAIL
            );
        }
    }

    /**
     * Send email (placeholder)
     */
    private function sendEmail(User $user, string $subject, string $message): void
    {
        // TODO: Implement email sending using Laravel Mail
        // Mail::to($user->email)->send(new ShipmentNotificationMail($subject, $message));
    }

    /**
     * Send SMS (placeholder)
     */
    private function sendSMS(User $user, string $message): void
    {
        // TODO: Implement SMS sending using Twilio
        // $twilio = new Twilio(config('services.twilio.account_sid'), config('services.twilio.auth_token'));
        // $twilio->messages->create($user->phone, ['from' => config('services.twilio.phone_number'), 'body' => $message]);
    }
}
