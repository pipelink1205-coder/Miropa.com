<?php

namespace App\Support;

use App\Events\MessageSent;
use App\Models\Message;
use Throwable;

class MessageBroadcaster
{
    public static function sent(Message $message): void
    {
        try {
            broadcast(new MessageSent($message->loadMissing(['sender', 'conversation.listing'])))->toOthers();
        } catch (Throwable $e) {
            report($e);
        }
    }
}
