<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shipment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tracking_number',
        'sender_id',
        'receiver_name',
        'receiver_email',
        'receiver_phone',
        'origin_address',
        'origin_city',
        'origin_state',
        'origin_postal_code',
        'destination_address',
        'destination_city',
        'destination_state',
        'destination_postal_code',
        'weight',
        'dimensions',
        'contents_description',
        'status',
        'shipping_cost',
        'pickup_date',
        'delivery_date',
        'notes',
    ];

    protected $casts = [
        'pickup_date' => 'datetime',
        'delivery_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Shipment statuses
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PICKED = 'picked';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_RETURNED = 'returned';

    /**
     * Generate unique tracking number
     */
    public static function generateTrackingNumber()
    {
        return 'TRK' . strtoupper(uniqid()) . rand(1000, 9999);
    }

    /**
     * Relationships
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class);
    }

    public function tracking()
    {
        return $this->hasMany(TrackingUpdate::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', self::STATUS_DELIVERED);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_PENDING,
            self::STATUS_PICKED,
            self::STATUS_IN_TRANSIT,
            self::STATUS_OUT_FOR_DELIVERY,
        ]);
    }
}
