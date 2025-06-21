<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderResource\Widgets\OrderStats;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords {
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array {
        return [
            OrderStats::class
        ];
    }

    public function getTabs(): array {
        return [
            null => Tab::make('All'),
            'Processing' => Tab::make()
                ->query(fn($query) => $query->where('status', 'processing')),
            'Confirmed' => Tab::make()
                ->query(fn($query) => $query->where('status', 'confirmed')),
            'Shipping' => Tab::make()
                ->query(fn($query) => $query->where('status', 'shipping')),
            'Shipped' => Tab::make()
                ->query(fn($query) => $query->where('status', 'shipped')),
            'Delivered' => Tab::make()
                ->query(fn($query) => $query->where('status', 'delivered')),
            'Cancelled' => Tab::make()
                ->query(fn($query) => $query->where('status', 'cancelled')),
            'Refunded' => Tab::make()
                ->query(fn($query) => $query->where('status', 'refunded')),
        ];
    }
}
