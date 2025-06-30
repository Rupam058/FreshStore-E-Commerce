<?php

namespace App\Livewire;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class MyOrdersPage extends Component {
    use WithPagination;

    public function render() {
        $my_orders = Order::where('user_id', Auth::user()->id)->latest()->paginate(6);
        return view('livewire.my-orders-page', [
            'orders' => $my_orders
        ]);
    }
}
