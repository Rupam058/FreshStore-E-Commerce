<?php

namespace App\Livewire;

use App\Helpers\CartDatabase;
use App\Helpers\CartManagement;
use App\Livewire\Partials\Navbar;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Jantinnerezo\LivewireAlert\Enums\Position;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Products - FreshStore')]

class ProductsPage extends Component {
    use WithPagination;

    #[Url]
    public $selected_categories = [];

    #[Url]
    public $selected_brands = [];

    #[Url]
    public $featured;

    #[Url]
    public $on_sale;

    #[Url]
    public $price_range = 200000;

    #[Url]
    public $sort = 'oldest';

    // add product to cart
    // public function addToCart($product_id) {
    //     $total_count = CartManagement::addItemToCart($product_id);

    //     $this->dispatch('update-cart-count', $total_count)->to(Navbar::class);

    //     LivewireAlert::title('Success')
    //         ->text('Item Added to Cart successfully.')
    //         ->success()
    //         ->position(Position::BottomEnd)
    //         ->timer(3000) // Dismisses after 3 seconds
    //         ->toast()
    //         ->show();
    // }
    // add product to cart
    public function addToCart($product_id) {
        // Check if user is authenticated
        if (!Auth::check()) {
            LivewireAlert::title('Login Required')
                ->text('Please login to add items to cart.')
                ->warning()
                ->position(Position::BottomEnd)
                ->timer(3000)
                ->toast()
                ->show();
            return;
        }

        try {
            $total_count = CartDatabase::addItemToCart($product_id);

            $this->dispatch('update-cart-count', total_count: $total_count)->to(Navbar::class);

            LivewireAlert::title('Success')
                ->text('Item added to cart successfully.')
                ->success()
                ->position(Position::BottomEnd)
                ->timer(3000)
                ->toast()
                ->show();
        } catch (\Exception $e) {
            LivewireAlert::title('Error')
                ->text('Unable to add item to cart. Please try again.')
                ->error()
                ->position(Position::BottomEnd)
                ->timer(3000)
                ->toast()
                ->show();
        }
    }

    public function render() {
        $productQuery = Product::query()->where('is_active', 1);

        if (!empty($this->selected_categories)) {
            $productQuery->whereIn('category_id', $this->selected_categories);
        }
        if (!empty($this->selected_brands)) {
            $productQuery->whereIn('brand_id', $this->selected_brands);
        }
        if ($this->featured) {
            $productQuery->where('is_featured', 1);
        }
        if ($this->on_sale) {
            $productQuery->where('on_sale', 1);
        }
        if ($this->price_range) {
            $productQuery->whereBetween('price', [0, $this->price_range]);
        }
        if ($this->sort == 'latest') {
            $productQuery->latest();
        }
        if ($this->sort == 'oldest') {
            $productQuery->orderBy('created_at', 'asc'); // DEFAULT
        }
        if ($this->sort == 'price') {
            $productQuery->orderBy('price');
        }

        return view('livewire.products-page', [
            'products' => $productQuery->paginate(9),
            'brands' => Brand::where('is_active', 1)->get(['id', 'name', 'slug']),
            'categories' => Category::where('is_active', 1)->get(['id', 'name', 'slug']),
            'is_authenticated' => Auth::check()
        ]);
    }
}
