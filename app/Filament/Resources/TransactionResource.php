<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Transacciones';
    protected static ?string $modelLabel = 'Transacción';
    protected static ?string $pluralModelLabel = 'Transacciones';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                Tables\Columns\TextColumn::make('listing.title')
                    ->label('Anuncio')
                    ->limit(30)
                    ->searchable(),
                Tables\Columns\TextColumn::make('buyer.name')
                    ->label('Comprador')
                    ->searchable(),
                Tables\Columns\TextColumn::make('seller.name')
                    ->label('Vendedor')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto')
                    ->money('COP', locale: 'es_CO')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'gray'    => 'pending',
                        'info'    => 'paid',
                        'warning' => 'shipped',
                        'primary' => 'delivered',
                        'success' => 'completed',
                        'danger'  => fn ($state) => in_array($state, ['cancelled', 'disputed']),
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending'   => 'Pendiente',
                        'paid'      => 'Pagado',
                        'shipped'   => 'Enviado',
                        'delivered' => 'Entregado',
                        'completed' => 'Completado',
                        'cancelled' => 'Cancelado',
                        'disputed'  => 'En disputa',
                        default     => $state,
                    }),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Método de pago')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'cash'     => 'Efectivo',
                        'transfer' => 'Transferencia',
                        'card'     => 'Tarjeta',
                        default    => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->date('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending'   => 'Pendiente',
                        'paid'      => 'Pagado',
                        'shipped'   => 'Enviado',
                        'delivered' => 'Entregado',
                        'completed' => 'Completado',
                        'cancelled' => 'Cancelado',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordUrl(null);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::whereIn('status', ['pending', 'paid', 'shipped'])->count();
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
