<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shipment_id',
        'type',
        'channel',
        'subject',
        'message',
        'is_read',
        'sent_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Notification types
     */
    const TYPE_SHIPMENT_CREATED = 'shipment_created';
    const TYPE_SHIPMENT_PICKED = 'shipment_picked';
    const TYPE_SHIPMENT_IN_TRANSIT = 'shipment_in_transit';
    const TYPE_OUT_FOR_DELIVERY = 'out_for_delivery';
    const TYPE_DELIVERED = 'delivered';
    const TYPE_DELIVERY_FAILED = 'delivery_failed';
    const TYPE_PAYMENT_RECEIVED = 'payment_received';
    const TYPE_INVOICE_GENERATED = 'invoice_generated';

    /**
     * Notification channels
     */
    const CHANNEL_EMAIL = 'email';
    const CHANNEL_SMS = 'sms';
    const CHANNEL_IN_APP = 'in_app';
    const CHANNEL_PUSH = 'push';

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}
