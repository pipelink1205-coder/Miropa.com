<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    public function view(User $user, Conversation $conversation): bool
    {
        if ($user->is_admin && $user->status === 'active') {
            return true;
        }

        return $user->id === $conversation->buyer_id
            || $user->id === $conversation->seller_id;
    }

    public function sendMessage(User $user, Conversation $conversation): bool
    {
        return $this->view($user, $conversation);
    }
}
