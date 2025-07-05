<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model {
    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'unit_amount',
        'total_amount',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function cart() {
        return $this->belongsTo(Cart::class);
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }

    protected static function boot() {
        parent::boot();
        static::saving(function ($cartItem) {
            $cartItem->total_amount
                = $cartItem->quantity * $cartItem->unit_amount;
        });
    }
    /**
     * Increment the quantity of the cart item.
     */
    public function incrementQuantity(int $amount = 1): void {
        $this->increment('quantity', $amount);
        $this->updateTotalAmount();
    }

    /**
     * Decrement the quantity of the cart item.
     */
    public function decrementQuantity(int $amount = 1): void {
        if ($this->quantity > $amount) {
            $this->decrement('quantity', $amount);
            $this->updateTotalAmount();
        }
    }

    /**
     * Update the total amount based on quantity and unit amount.
     */
    public function updateTotalAmount(): void {
        $this->update([
            'total_amount' => $this->quantity * $this->unit_amount
        ]);
    }
}
