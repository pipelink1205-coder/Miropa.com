<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportResource\Pages;
use App\Models\Report;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationLabel = 'Reportes';

    protected static ?string $modelLabel = 'Reporte';

    protected static ?string $pluralModelLabel = 'Reportes';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reporter.name')
                    ->label('Denunciante')
                    ->searchable(),
                Tables\Columns\TextColumn::make('reportable_type')
                    ->label('Tipo')
                    ->formatStateUsing(fn ($state) => match (class_basename($state)) {
                        'Listing' => 'Anuncio',
                        'User' => 'Usuario',
                        'Message' => 'Mensaje',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Motivo')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'spam' => 'Spam',
                        'fraud' => 'Fraude',
                        'inappropriate' => 'Inapropiado',
                        'prohibited' => 'Artículo prohibido',
                        'other' => 'Otro',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(50),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'open',
                        'info' => 'reviewing',
                        'success' => 'resolved',
                        'gray' => 'dismissed',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'open' => 'Abierto',
                        'reviewing' => 'En revisión',
                        'resolved' => 'Resuelto',
                        'dismissed' => 'Descartado',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'open' => 'Abierto',
                        'reviewing' => 'En revisión',
                        'resolved' => 'Resuelto',
                        'dismissed' => 'Descartado',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('resolver')
                    ->label('Resolver')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Report $record) => $record->status === 'open')
                    ->action(fn (Report $record) => $record->update([
                        'status' => 'resolved',
                        'resolved_by' => auth()->id(),
                    ])),
                Tables\Actions\Action::make('descartar')
                    ->label('Descartar')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (Report $record) => $record->status === 'open')
                    ->action(fn (Report $record) => $record->update([
                        'status' => 'dismissed',
                        'resolved_by' => auth()->id(),
                    ])),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReports::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'open')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
