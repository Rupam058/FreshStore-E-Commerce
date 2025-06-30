<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrdersRelationManager extends RelationManager {
    protected static string $relationship = 'orders';

    public function form(Form $form): Form {
        return $form
            ->schema([
                // Forms\Components\TextInput::make('id')
                //     ->required()
                //     ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('id')
                    ->label('Order ID')
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
                        'failed' => 'Failed'
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
                        'shipping' => 'info',
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
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Action::make('View Order')
                    ->url(fn(Order $record): string => OrderResource::getUrl(
                        'view',
                        ['record' => $record]
                    ))
                    ->color('info')
                    ->icon('heroicon-o-eye'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
