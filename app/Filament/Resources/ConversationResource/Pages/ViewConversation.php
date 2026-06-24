<?php

namespace App\Filament\Resources\ConversationResource\Pages;

use App\Filament\Resources\ConversationResource;
use Filament\Resources\Pages\ViewRecord;

class ViewConversation extends ViewRecord
{
    protected static string $resource = ConversationResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->record->load([
            'listing',
            'buyer',
            'seller',
            'messages.sender',
        ]);
    }
}
