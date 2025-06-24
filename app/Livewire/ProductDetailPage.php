<?php

namespace App\Livewire;

use App\Helpers\CartManagement;
use App\Livewire\Partials\Navbar;
use App\Models\Product;
use Jantinnerezo\LivewireAlert\Enums\Position;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Product Detail - FreshStore')]
class ProductDetailPage extends Component {

    public $slug;
    public $quantity = 1;

    public function mount($slug) {
        $this->slug = $slug;
    }
    public function increaseQty() {
        $this->quantity++;
    }
    public function decreaseQty() {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    // add product to cart
    public function addToCart($product_id) {
        $total_count = CartManagement::addItemToCart($product_id);

        $this->dispatch('update-cart-count', $total_count)->to(Navbar::class);

        LivewireAlert::title('Success')
            ->text('Item Added to Cart successfully.')
            ->success()
            ->position(Position::BottomEnd)
            ->timer(3000) // Dismisses after 3 seconds
            ->toast()
            ->show();
    }

    public function render() {
        return view('livewire.product-detail-page', [
            'product' => Product::where('slug', $this->slug)->firstOrFail()
        ]);
    }
}
