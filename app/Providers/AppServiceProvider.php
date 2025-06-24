<?php

namespace App\Providers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Observers\BrandObserver;
use App\Observers\CategoryObserver;
use App\Observers\ProductObserver;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider {
    /**
     * Register any application services.
     */
    public function register(): void {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void {
        Category::observe(CategoryObserver::class);
        Brand::observe(BrandObserver::class);
        Product::observe(ProductObserver::class);

        FilamentAsset::register([
            // Or via CDN
            Js::make('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11'),
        ]);
    }
}
