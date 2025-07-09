<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model {
    protected $fillable = [
        'user_id',
        'order_id',
        'first_name',
        'last_name',
        'phone',
        'street_address',
        'city',
        'state',
        'zip_code'
    ];

    public function order() {
        return $this->belongsTo(Order::class);
    }

    public function getFullNameAttribute() {
        return "{$this->first_name} {$this->last_name}";
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function getFullAddressAttribute() {
        return "{$this->street_address}, {$this->city}, {$this->state} {$this->zip_code}";
    }

    public static function findSimilarForUser($userId, array $addressData) {
        return static::where('user_id', $userId)
            ->where('first_name', $addressData['first_name'])
            ->where('last_name', $addressData['last_name'])
            ->where('phone', $addressData['phone'])
            ->where('street_address', $addressData['street_address'])
            ->where('city', $addressData['city'])
            ->where('state', $addressData['state'])
            ->where('zip_code', $addressData['zip_code'])
            ->whereNull('order_id') // Only check user addresses, not order-specific ones
            ->first();
    }
}
