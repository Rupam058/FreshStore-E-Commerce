<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class OrderStats extends BaseWidget {
    protected function getStats(): array {
        return [
            Stat::make(
                'New Confirmed Orders',
                Order::query()->where('status', 'confirmed')->count()
            ),

            Stat::make(
                'Order Processing',
                Order::query()->where('status', 'processing')->count()
            ),
            Stat::make(
                'Order Delivered',
                Order::query()->where('status', 'delivered')->count()
            ),
            Stat::make(
                'Average Order Value',
                Number::currency(Order::query()->avg('grand_total'), 'INR')
            ),
        ];
    }
}
