<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Usuarios';

    protected static ?string $modelLabel = 'Usuario';

    protected static ?string $pluralModelLabel = 'Usuarios';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Información personal')->schema([
                Forms\Components\TextInput::make('name')->label('Nombre')->required(),
                Forms\Components\TextInput::make('username')->label('Usuario')->required(),
                Forms\Components\TextInput::make('email')->label('Email')->email()->required(),
                Forms\Components\TextInput::make('phone')->label('Teléfono'),
            ])->columns(2),

            Forms\Components\Section::make('Estado y permisos')->schema([
                Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->options([
                        'active' => 'Activo',
                        'suspended' => 'Suspendido',
                        'banned' => 'Baneado',
                    ])
                    ->required(),
                Forms\Components\Toggle::make('is_admin')->label('Es administrador'),
                Forms\Components\Toggle::make('is_verified')->label('Verificado'),
                Forms\Components\Select::make('verification_level')
                    ->label('Nivel de verificación')
                    ->options([
                        'none' => 'Ninguno',
                        'email' => 'Email',
                        'phone' => 'Teléfono',
                        'id_document' => 'Documento de identidad',
                    ]),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('username')
                    ->label('Usuario')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'suspended',
                        'danger' => 'banned',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'active' => 'Activo',
                        'suspended' => 'Suspendido',
                        'banned' => 'Baneado',
                        default => $state,
                    }),
                Tables\Columns\IconColumn::make('is_admin')
                    ->label('Admin')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_verified')
                    ->label('Verificado')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registrado')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'active' => 'Activo',
                        'suspended' => 'Suspendido',
                        'banned' => 'Baneado',
                    ]),
                Tables\Filters\TernaryFilter::make('is_admin')->label('Administrador'),
                Tables\Filters\TernaryFilter::make('is_verified')->label('Verificado'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Editar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Eliminar'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }
}
