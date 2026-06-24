<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConversationResource\Pages;
use App\Models\Conversation;
use Filament\Forms\Form;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ConversationResource extends Resource
{
    protected static ?string $model = Conversation::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'Conversaciones';

    protected static ?string $modelLabel = 'Conversación';

    protected static ?string $pluralModelLabel = 'Conversaciones';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Contexto')->schema([
                    TextEntry::make('listing.title')->label('Anuncio'),
                    TextEntry::make('listing.slug')
                        ->label('URL pública')
                        ->formatStateUsing(fn (?string $state) => $state ? url("/anuncios/{$state}") : '—')
                        ->copyable(),
                    TextEntry::make('buyer.name')->label('Comprador'),
                    TextEntry::make('buyer.email')->label('Email comprador'),
                    TextEntry::make('seller.name')->label('Vendedor'),
                    TextEntry::make('seller.email')->label('Email vendedor'),
                    TextEntry::make('created_at')->label('Iniciada')->dateTime('d/m/Y H:i'),
                    TextEntry::make('last_message_at')->label('Último mensaje')->dateTime('d/m/Y H:i'),
                ])->columns(2),
                Section::make('Mensajes')
                    ->schema([
                        RepeatableEntry::make('messages')
                            ->label('')
                            ->schema([
                                TextEntry::make('sender.name')->label('Remitente'),
                                TextEntry::make('body')->label('Mensaje')->columnSpanFull(),
                                TextEntry::make('created_at')->label('Enviado')->dateTime('d/m/Y H:i'),
                                TextEntry::make('read_at')
                                    ->label('Leído')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('Sin leer'),
                            ])
                            ->columns(3)
                            ->columnSpanFull(),
                    ]),
            ]);
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
                    ->limit(28)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('buyer.name')
                    ->label('Comprador')
                    ->searchable(),
                Tables\Columns\TextColumn::make('seller.name')
                    ->label('Vendedor')
                    ->searchable(),
                Tables\Columns\TextColumn::make('lastMessage.body')
                    ->label('Último mensaje')
                    ->limit(45)
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('messages_count')
                    ->label('Msgs')
                    ->counts('messages')
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_message_at')
                    ->label('Actividad')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with(['listing', 'buyer', 'seller', 'lastMessage'])
                ->withCount('messages'))
            ->filters([
                Tables\Filters\Filter::make('recientes')
                    ->label('Actividad últimos 7 días')
                    ->query(fn (Builder $query) => $query->where('last_message_at', '>=', now()->subDays(7))),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Ver hilo'),
            ])
            ->defaultSort('last_message_at', 'desc')
            ->emptyStateHeading('Sin conversaciones')
            ->emptyStateDescription('Cuando un comprador contacte a un vendedor, aparecerá aquí.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConversations::route('/'),
            'view' => Pages\ViewConversation::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::query()
            ->where('last_message_at', '>=', now()->subDay())
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }
}
