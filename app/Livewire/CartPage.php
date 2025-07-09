<?php

namespace App\Livewire;

use App\Helpers\CartDatabase;
use App\Helpers\CartManagement;
use App\Livewire\Partials\Navbar;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Cart - FreshStore')]

class CartPage extends Component {

    public $cart_items = [];
    public $grand_total = 0;
    public $is_authenticated = false;

    public function mount() {
        // $this->cart_items = CartManagement::getCartItemsFromCookie();
        // $this->grand_total = CartManagement::calculateGrandTotal($this->cart_items);

        $this->is_authenticated = Auth::check();
        if ($this->is_authenticated) {
            $this->loadCartData();
        }
    }

    private function loadCartData() {
        try {
            $this->cart_items = CartDatabase::getCartItemsFromDatabase();
            $this->grand_total = CartDatabase::calculateGrandTotal($this->cart_items);
        } catch (\Exception $e) {
            session()->flash('error', 'Unable to load cart data. Please try again');
            $this->cart_items = [];
            $this->grand_total = 0;
        }
    }

    public function removeItem($product_id) {
        // $this->cart_items = CartManagement::removeCartItem($product_id);
        // $this->grand_total = CartManagement::calculateGrandTotal($this->cart_items);
        // $this->dispatch('update-cart-count', total_count: count($this->cart_items))->to(Navbar::class);

        if (!$this->is_authenticated) {
            session()->flash('error', 'Please login to manage your cart.');
            return;
        }
        try {
            $this->cart_items = CartDatabase::removeCartItem($product_id);
            $this->grand_total = CartDatabase::calculateGrandTotal($this->cart_items);

            // Update cart count in navbar
            $cart_count = CartDatabase::getCartItemsCount();
            $this->dispatch('update-cart-count', total_count: $cart_count)->to(Navbar::class);
        } catch (\Exception $e) {
            session()->flash('error', 'Unable to remove item. Please try again.');
        }
    }
    public function increaseQty($product_id) {
        // $this->cart_items = CartManagement::incrementQuantityToCartItem($product_id);
        // $this->grand_total = CartManagement::calculateGrandTotal($this->cart_items);

        $this->cart_items = CartDatabase::incrementQuantityToCartItem($product_id);
        $this->grand_total = CartDatabase::calculateGrandTotal($this->cart_items);

        // Update cart count in navbar
        $cart_count = CartDatabase::getCartItemsCount();
        $this->dispatch('update-cart-count', total_count: $cart_count)->to(Navbar::class);
    }
    public function decreaseQty($product_id) {
        // $this->cart_items = CartManagement::decrementQuantityToCartItem($product_id);
        // $this->grand_total = CartManagement::calculateGrandTotal($this->cart_items);

        $this->cart_items = CartDatabase::decrementQuantityToCartItem($product_id);
        $this->grand_total = CartDatabase::calculateGrandTotal();

        // Update cart count in navbar
        $cart_count = CartDatabase::getCartItemsCount();
        $this->dispatch('update-cart-count', total_count: $cart_count)->to(Navbar::class);
    }

    public function clearCart() {
        CartDatabase::clearCart();
        $this->cart_items = [];
        $this->grand_total = 0;

        // Update cart count in navbar
        $this->dispatch('update-cart-count', total_count: 0)->to(Navbar::class);
    }

    public function render() {
        return view('livewire.cart-page');
    }
}
