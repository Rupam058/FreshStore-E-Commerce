<?php

namespace App\Livewire;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Stripe\Checkout\Session;
use Stripe\Stripe;

#[Title('Success - FreshStore')]

class SuccessPage extends Component {

    // Fetching the session id
    #[Url()]
    public $session_id;

    public function render() {
        // fetching the latest order with address
        $latest_order = Order::with('address')
            ->where('user_id', Auth::user()->id)
            ->latest()
            ->first();

        // If there is session id meaning stripe payment is done
        // we are retrieving it.
        if ($this->session_id) {
            Stripe::setApiKey(env('STRIPE_SECRET'));
            $session_info = Session::retrieve($this->session_id);

            // if you dd($session_id)
            // payment_status is a session attribute
            if ($session_info->payment_status != 'paid') {
                $latest_order->payment_status = 'failed';
                $latest_order->save();
                return redirect()->route('cancel');
            } else if ($session_info->payment_status == 'paid') {
                $latest_order->payment_status = 'paid';
                $latest_order->save();
            }
        }

        return view('livewire.success-page', [
            'order' => $latest_order,
        ]);
    }
}
