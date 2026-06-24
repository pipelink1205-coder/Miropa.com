<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Categorías';

    protected static ?string $modelLabel = 'Categoría';

    protected static ?string $pluralModelLabel = 'Categorías';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('name')->label('Nombre')->disabled(),
                Forms\Components\TextInput::make('slug')->label('Slug')->disabled(),
                Forms\Components\TextInput::make('vertical')->label('Vertical')->disabled(),
                Forms\Components\TextInput::make('level')->label('Nivel')->disabled(),
                Forms\Components\Select::make('sale_mode')
                    ->label('Modo de venta')
                    ->options([
                        'marketplace' => 'Marketplace',
                        'classified' => 'Trato directo',
                    ])
                    ->disabled(),
                Forms\Components\Toggle::make('is_active')->label('Activa'),
                Forms\Components\TextInput::make('position')->label('Posición')->numeric(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->label('Slug')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('parent.name')->label('Padre')->toggleable(),
                Tables\Columns\TextColumn::make('vertical')->label('Vertical')->badge(),
                Tables\Columns\TextColumn::make('level')->label('Nivel')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('sale_mode')
                    ->label('Modo')
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'classified' => 'Trato directo',
                        'marketplace' => 'Marketplace',
                        default => $state ?? '—',
                    }),
                Tables\Columns\IconColumn::make('is_active')->label('Activa')->boolean(),
                Tables\Columns\TextColumn::make('listings_count')->label('Anuncios')->counts('listings'),
                Tables\Columns\TextColumn::make('position')->label('Pos.')->sortable(),
            ])
            ->defaultSort('position')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Activa'),
                Tables\Filters\SelectFilter::make('vertical')
                    ->options(['moda' => 'Moda', 'general' => 'General']),
                Tables\Filters\SelectFilter::make('sale_mode')
                    ->label('Modo')
                    ->options(['marketplace' => 'Marketplace', 'classified' => 'Trato directo']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
