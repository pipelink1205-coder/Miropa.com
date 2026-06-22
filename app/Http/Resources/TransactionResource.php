<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'listing' => new ListingResource($this->whenLoaded('listing')),
            'buyer' => new UserResource($this->whenLoaded('buyer')),
            'seller' => new UserResource($this->whenLoaded('seller')),
            'amount' => $this->amount,
            'amount_formatted' => 'COP '.number_format((float) $this->amount, 0, ',', '.'),
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'shipping_method' => $this->shipping_method,
            'completed_at' => $this->completed_at,
            'created_at' => $this->created_at,
        ];
    }
}
