<?php

namespace App\Livewire;

use App\Helpers\CartDatabase;
use App\Helpers\CartManagement;
use App\Mail\OrderPlaced;
use App\Models\Address;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Title;
use Livewire\Component;
use Stripe\Checkout\Session;
use Stripe\Stripe;

#[Title('Checkout')]

class CheckoutPage extends Component {

    public $first_name;
    public $last_name;
    public $phone;
    public $street_address;
    public $city;
    public $state;
    public $zip_code;
    public $payment_method;

    // Address selection propterties
    public $selected_address_id = null;
    public $use_saved_address = false;
    public $saved_addresses = [];

    public function mount() {
        $cart_items = CartDatabase::getCartItemsFromDatabase();
        if (count($cart_items) == 0) {
            return redirect('/products');
        }

        // Load user's saved Addresses
        $this->loadSavedAddresses();
    }

    public function loadSavedAddresses() {
        $addresses = Auth::user()->getSavedAddresses();

        // Convert to array but include the accessor methods
        $this->saved_addresses = $addresses->map(function ($address) {
            return [
                'id' => $address->id,
                'first_name' => $address->first_name,
                'last_name' => $address->last_name,
                'phone' => $address->phone,
                'street_address' => $address->street_address,
                'city' => $address->city,
                'state' => $address->state,
                'zip_code' => $address->zip_code,
                'full_name' => $address->full_name, // This uses the accessor
                'full_address' => $address->full_address, // This uses the accessor
                'created_at' => $address->created_at,
                'updated_at' => $address->updated_at,
            ];
        })->toArray();
    }
    public function updatedSelectedAddressId($value) {
        if ($value && $this->use_saved_address) {
            $address = Address::find($value);
            if ($address && $address->user_id === Auth::id()) {
                $this->fillAddressFields($address);
            }
        }
    }
    public function updatedUseSavedAddress($value) {
        if ($value && $this->selected_address_id) {
            $address = Address::find($this->selected_address_id);
            if ($address && $address->user_id === Auth::id()) {
                $this->fillAddressFields($address);
            }
        } else {
            $this->clearAddressFields();
        }
    }
    private function fillAddressFields($address) {
        $this->first_name = $address->first_name;
        $this->last_name = $address->last_name;
        $this->phone = $address->phone;
        $this->street_address = $address->street_address;
        $this->city = $address->city;
        $this->state = $address->state;
        $this->zip_code = $address->zip_code;
    }

    private function clearAddressFields() {
        $this->first_name = '';
        $this->last_name = '';
        $this->phone = '';
        $this->street_address = '';
        $this->city = '';
        $this->state = '';
        $this->zip_code = '';
    }

    public function placeOrder() {
        $this->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'phone' => 'required',
            'street_address' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip_code' => 'required',
            'payment_method' => 'required'
        ]);

        $cart_items = CartDatabase::getCartItemsFromDatabase();

        $line_items = [];

        foreach ($cart_items as $item) {
            $line_items[] = [
                'price_data' => [
                    'currency' => 'inr',
                    'unit_amount' => $item['unit_amount'] * 100,
                    'product_data' => [
                        'name' => $item['name']
                    ],
                ],
                'quantity' => $item['quantity'],
            ];
        }

        // Order model
        $order = new Order();
        $order->user_id = Auth::user()->id;
        $order->grand_total = CartDatabase::calculateGrandTotal($cart_items);
        $order->payment_method = $this->payment_method;
        $order->payment_status = 'pending';
        $order->status = 'confirmed';
        $order->currency = 'inr';
        $order->shipping_amount = 0;
        $order->shipping_method = 'none';
        $order->notes = 'Order placed by ' . Auth::user()->name;

        // Address model
        // $address = new Address();
        // $address->first_name = $this->first_name;
        // $address->last_name = $this->last_name;
        // $address->phone = $this->phone;
        // $address->street_address = $this->street_address;
        // $address->city = $this->city;
        // $address->state = $this->state;
        // $address->zip_code = $this->zip_code;

        $redirect_url = '';

        // if stripe, else cod 
        if ($this->payment_method == 'stripe') {
            Stripe::setApiKey(env('STRIPE_SECRET'));

            // Stripe Session
            $session_checkout = Session::create([
                'payment_method_types' => ['card'],
                'customer_email' => Auth::user()->email,
                'line_items' => $line_items,
                'mode' => 'payment',
                'success_url' => route('success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('cancel'),
            ]);

            $redirect_url = $session_checkout->url;
        } else {
            $redirect_url = route('success');
        }

        $order->save();
        // $address->order_id = $order->id;
        // $address->save();

        // Handle address saving/updating
        $address = $this->handleAddress();

        // Create order-specific address record
        $orderAddress = $address->replicate();
        $orderAddress->order_id = $order->id;
        $orderAddress->save();

        // Create order items from cart items
        $order_items = [];
        foreach ($cart_items as $item) {
            $order_items[] = [
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_amount' => $item['unit_amount'],
                'total_amount' => $item['total_amount'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $order->items()->createMany($order_items);
        // CartManagement::clearCart();
        CartDatabase::clearCart();

        // send mail
        Mail::to(request()->user())->send(new OrderPlaced($order));
        return redirect($redirect_url);
    }

    private function handleAddress() {
        $addressData = [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'street_address' => $this->street_address,
            'city' => $this->city,
            'state' => $this->state,
            'zip_code' => $this->zip_code,
        ];

        if ($this->use_saved_address && $this->selected_address_id) {
            // Update existing address
            $address = Address::find($this->selected_address_id);
            if ($address && $address->user_id === Auth::id()) {
                $address->update($addressData);
                return $address;
            }
        }

        // Check if similar address exists
        $existingAddress = Address::findSimilarForUser(Auth::id(), $addressData);

        if ($existingAddress) {
            $existingAddress->update($addressData);
            return $existingAddress;
        }

        // Create new address
        $addressData['user_id'] = Auth::id();
        $address = Address::create($addressData);

        return $address;
    }

    public function clearAddressForm() {
        // Clear all address fields
        $this->clearAddressFields();

        // Reset address selection state
        $this->selected_address_id = null;
        $this->use_saved_address = false;
    }

    public function render() {
        // $cart_items = CartManagement::getCartItemsFromCookie();
        // $grand_total = CartManagement::calculateGrandTotal($cart_items);
        $cart_items = CartDatabase::getCartItemsFromDatabase();
        $grand_total = CartDatabase::calculateGrandTotal($cart_items);

        return view('livewire.checkout-page', [
            'cart_items' => $cart_items,
            'grand_total' => $grand_total,
        ]);
    }
}
