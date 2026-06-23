<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IdentityVerificationResource\Pages;
use App\Models\IdentityVerification;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class IdentityVerificationResource extends Resource
{
    protected static ?string $model = IdentityVerification::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationLabel = 'Verificaciones';

    protected static ?string $modelLabel = 'Verificación';

    protected static ?string $pluralModelLabel = 'Verificaciones de identidad';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('document_type')
                    ->label('Tipo de documento')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'cedula' => 'Cédula',
                        'passport' => 'Pasaporte',
                        'foreign_id' => 'Cédula extranjera',
                        default => $state,
                    }),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'Pendiente',
                        'approved' => 'Aprobada',
                        'rejected' => 'Rechazada',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Enviado')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reviewed_at')
                    ->label('Revisado')
                    ->date('d/m/Y')
                    ->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'approved' => 'Aprobada',
                        'rejected' => 'Rechazada',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('ver_frente')
                    ->label('Ver frente')
                    ->icon('heroicon-o-eye')
                    ->url(fn (IdentityVerification $record) => route('identity.document', [
                        'verification' => $record,
                        'side' => 'front',
                    ]))
                    ->openUrlInNewTab()
                    ->visible(fn (IdentityVerification $record) => (bool) $record->document_path),
                Tables\Actions\Action::make('ver_reverso')
                    ->label('Ver reverso')
                    ->icon('heroicon-o-document-duplicate')
                    ->url(fn (IdentityVerification $record) => $record->document_back_path
                        ? route('identity.document', ['verification' => $record, 'side' => 'back'])
                        : null)
                    ->openUrlInNewTab()
                    ->visible(fn (IdentityVerification $record) => (bool) $record->document_back_path),
                Tables\Actions\Action::make('aprobar')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (IdentityVerification $record) => $record->status === 'pending')
                    ->action(function (IdentityVerification $record) {
                        $record->update([
                            'status' => 'approved',
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);
                        $record->user->update([
                            'is_verified' => true,
                            'verification_level' => 'id_document',
                        ]);
                    }),
                Tables\Actions\Action::make('rechazar')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (IdentityVerification $record) => $record->status === 'pending')
                    ->action(fn (IdentityVerification $record) => $record->update([
                        'status' => 'rejected',
                        'reviewed_by' => auth()->id(),
                        'reviewed_at' => now(),
                    ])),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIdentityVerifications::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();

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
