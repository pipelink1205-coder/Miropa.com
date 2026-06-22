<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $other = $this->buyer_id === $user->id ? $this->seller : $this->buyer;

        return [
            'id' => $this->id,
            'listing' => new ListingResource($this->whenLoaded('listing')),
            'other_user' => new UserResource($other),
            'last_message' => new MessageResource($this->whenLoaded('lastMessage')),
            'unread_count' => $this->messages->where('read_at', null)->where('sender_id', '!=', $user->id)->count(),
            'created_at' => $this->created_at,
        ];
    }
}
