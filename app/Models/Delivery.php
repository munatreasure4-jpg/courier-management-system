<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'delivery_personnel_id',
        'current_latitude',
        'current_longitude',
        'status',
        'estimated_delivery_time',
        'actual_delivery_time',
        'signature_url',
        'photo_url',
        'delivery_notes',
        'recipient_name',
    ];

    protected $casts = [
        'estimated_delivery_time' => 'datetime',
        'actual_delivery_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Delivery statuses
     */
    const STATUS_ASSIGNED = 'assigned';
    const STATUS_PICKED_UP = 'picked_up';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_ARRIVING = 'arriving';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_FAILED = 'failed';

    /**
     * Relationships
     */
    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function deliveryPersonnel()
    {
        return $this->belongsTo(User::class, 'delivery_personnel_id');
    }

    public function gpsLocations()
    {
        return $this->hasMany(GPSTracking::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_ASSIGNED,
            self::STATUS_PICKED_UP,
            self::STATUS_IN_TRANSIT,
            self::STATUS_ARRIVING,
        ]);
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', self::STATUS_DELIVERED);
    }
}
