<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ListingResource\Pages;
use App\Models\Listing;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ListingResource extends Resource
{
    protected static ?string $model = Listing::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Anuncios';

    protected static ?string $modelLabel = 'Anuncio';

    protected static ?string $pluralModelLabel = 'Anuncios';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('title')->label('Título')->required(),
                Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->options([
                        'draft' => 'Borrador',
                        'active' => 'Activo',
                        'reserved' => 'Reservado',
                        'sold' => 'Vendido',
                        'paused' => 'Pausado',
                        'deleted' => 'Eliminado',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('description')->label('Descripción')->columnSpanFull(),
                Forms\Components\TextInput::make('price')->label('Precio')->numeric()->prefix('COP'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Vendedor')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->money('COP', locale: 'es_CO')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'reserved',
                        'gray' => 'draft',
                        'danger' => fn ($state) => in_array($state, ['deleted', 'sold']),
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'draft' => 'Borrador',
                        'active' => 'Activo',
                        'reserved' => 'Reservado',
                        'sold' => 'Vendido',
                        'paused' => 'Pausado',
                        'deleted' => 'Eliminado',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('views_count')
                    ->label('Vistas')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Publicado')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'active' => 'Activo',
                        'reserved' => 'Reservado',
                        'sold' => 'Vendido',
                        'draft' => 'Borrador',
                        'paused' => 'Pausado',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Editar'),
                Tables\Actions\DeleteAction::make()->label('Eliminar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Eliminar seleccionados'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListListings::route('/'),
            'edit' => Pages\EditListing::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('status', 'active')->count();
    }
}
