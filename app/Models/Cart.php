<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model {
    protected $fillable = [
        'user_id',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function cartItems() {
        return $this->hasMany(CartItem::class);
    }

    public function products() {
        return $this->belongsToMany(Product::class, 'cart_items')
            ->withPivot('quantity', 'unit_amount', 'total_amount')
            ->withTimestamps();
    }

    public function getGrandTotal() {
        return $this->cartItems->sum('total_amount');
    }

    public function getTotalItems() {
        return $this->cartItems->sum('quantity');
    }

    // Check if the cart is empty
    public function isEmpty(){
        return $this->cartItems->isEmpty();
    }

    // Clear all items from the cart
    public function clear(){
        $this->cartItems()->delete();
    }
}
