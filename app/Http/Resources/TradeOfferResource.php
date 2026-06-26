<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TradeOfferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'message' => $this->message,
            'proposer' => UserResource::make($this->whenLoaded('proposer')),
            'target_listing' => ListingResource::make($this->whenLoaded('targetListing')),
            'offered_listing' => ListingResource::make($this->whenLoaded('offeredListing')),
            'conversation_id' => $this->conversation_id,
            'responded_at' => $this->responded_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
