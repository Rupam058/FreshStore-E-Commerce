<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrders extends BaseWidget {
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 2;

    public function table(Table $table): Table {
        return $table
            ->query(OrderResource::getEloquentQuery())
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('Order ID')
                    ->searchable(),

                TextColumn::make('user.name')
                    ->searchable(),

                TextColumn::make('grand_total')
                    ->label('Total')
                    ->money('inr'),

                TextColumn::make('payment_method')
                    ->searchable()
                    ->badge()
                    ->formatStateUsing(fn($state) => [
                        'stripe' => 'Stripe',
                        'cod' => 'Cash on Delivery'
                    ][$state] ?? $state)
                    ->color(fn($state) => match ($state) {
                        'stripe' => 'success',
                        'cod' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('payment_status')
                    ->sortable()
                    ->badge()
                    ->formatStateUsing(fn($state) => [
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'Failed' => 'Failed'
                    ][$state] ?? $state)
                    ->color(fn($state) => match (strtolower($state)) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn($state) => [
                        'processing' => 'Processing',
                        'confirmed' => 'Confirmed',
                        'shipping' => 'Shipping',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                        'refunded' => 'Refunded',
                    ][$state] ?? $state)
                    ->color(fn(string $state): string => match ($state) {
                        'processing' => 'warning',
                        'confirmed' => 'info',
                        'shipping' => 'primary',
                        'shipped' => 'success',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        'refunded' => 'gray',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'processing' => 'heroicon-m-arrow-path',
                        'confirmed' => 'heroicon-m-sparkles',
                        'shipping' => 'heroicon-o-truck',
                        'shipped' => 'heroicon-m-truck',
                        'delivered' => 'heroicon-m-check-badge',
                        'cancelled' => 'heroicon-m-x-circle',
                        'refunded' => 'heroicon-m-arrow-uturn-left',
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Order Date')
                    ->dateTime()
                    ->sortable()
            ])
            ->actions([
                Action::make('View Order')
                    ->url(fn(Order $record): string => OrderResource::getUrl(
                        'view',
                        ['record' => $record]
                    ))
                    ->icon('heroicon-o-eye')
            ]);
    }
}
