<?php

namespace App\Helpers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class CartDatabase {
  public static function getOrCreateCart(): Cart {
    $user = Auth::user();
    if (!$user) {
      throw new \Exception('User must be authenticated to manage cart');
    }

    return Cart::firstOrCreate(['user_id' => $user->id]);
  }

  // Get all Cart Items from Database...
  public static function getCartItemsFromDatabase(): array {
    $cart = self::getOrCreateCart();

    return $cart->cartItems()
      ->with('product')
      ->get()
      ->map(function ($cartItem) {
        return [
          'product_id' => $cartItem->product_id,
          'name' => $cartItem->product->name,
          'image' => $cartItem->product->images[0] ?? null,
          'quantity' => $cartItem->quantity,
          'unit_amount' => $cartItem->unit_amount,
          'total_amount' => $cartItem->total_amount
        ];
      })
      ->toArray();
  }

  // Add item to cart
  public static function addItemToCart($product_id): int {
    $cart = self::getOrCreateCart();
    $product = Product::findOrFail($product_id);

    $cartItem = CartItem::where('cart_id', $cart->id)
      ->where('product_id', $product_id)
      ->first();

    if ($cartItem) {
      $cartItem->incrementQuantity();
    } else {
      CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $product_id,
        'quantity' => 1,
        'unit_amount' => $product->price,
      ]);
    }

    return $cart->fresh()->total_items;
  }

  // Remove item from Cart
  public static function removeCartItem($product_id): array {
    $cart = self::getOrCreateCart();

    CartItem::where('cart_id', $cart->id)
      ->where('product_id', $product_id)
      ->delete();

    return self::getCartItemsFromDatabase();
  }

  // Clear Cart
  public static function clearCart(): void {
    $cart = self::getOrCreateCart();
    $cart->clear();
  }

  // Increment item quantity.
  public static function incrementQuantityToCartItem($product_id): array {
    $cart = self::getOrCreateCart();

    $cartItem = CartItem::where('cart_id', $cart->id)
      ->where('product_id', $product_id)
      ->firstOrFail();

    $cartItem->incrementQuantity();

    return self::getCartItemsFromDatabase();
  }

  // Decrement item quantity.
  public static function decrementQuantityToCartItem($product_id): array {
    $cart = self::getOrCreateCart();

    $cartItem = CartItem::where('cart_id', $cart->id)
      ->where('product_id', $product_id)
      ->firstOrFail();

    if ($cartItem->quantity > 1) {
      $cartItem->decrementQuantity();
    } else {
      $cartItem->delete();
    }

    return self::getCartItemsFromDatabase();
  }

  // Calculate Grand Total
  public static function calculateGrandTotal($items = null): float {
    if ($items !== null) {
      return array_sum(array_column($items, 'total_amount'));
    }

    $cart = self::getOrCreateCart();
    return $cart->getGrandTotal();
  }

  // Get cart Item count
  public static function getCartItemsCount(): int {
    $cart = self::getOrCreateCart();
    return $cart->getTotalItems();
  }

  // check if cart is empty
  public static function isCartEmpty(): bool {
    $cart = self::getOrCreateCart();
    return $cart->isEmpty();
  }
}
