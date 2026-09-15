<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'status',
        'address',
        'city',
        'state',
        'postal_code',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * User roles
     */
    const ROLE_ADMIN = 'admin';
    const ROLE_DELIVERY = 'delivery_personnel';
    const ROLE_CUSTOMER = 'customer';

    /**
     * User statuses
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';

    /**
     * Relationships
     */
    public function shipments()
    {
        return $this->hasMany(Shipment::class, 'sender_id');
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'delivery_personnel_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'user_id');
    }

    /**
     * Scope: Get admin users
     */
    public function scopeAdmin($query)
    {
        return $query->where('role', self::ROLE_ADMIN);
    }

    /**
     * Scope: Get delivery personnel
     */
    public function scopeDeliveryPersonnel($query)
    {
        return $query->where('role', self::ROLE_DELIVERY);
    }

    /**
     * Scope: Get customers
     */
    public function scopeCustomer($query)
    {
        return $query->where('role', self::ROLE_CUSTOMER);
    }

    /**
     * Scope: Get active users
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
}
