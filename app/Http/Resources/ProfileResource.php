<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'rating_avg' => (float) $this->rating_avg,
            'ratings_count' => $this->ratings_count,
            'sales_count' => $this->sales_count,
            'purchases_count' => $this->purchases_count,
            'response_rate' => (float) $this->response_rate,
            'member_since' => $this->member_since?->toDateString(),
            'location' => $this->whenLoaded('location', fn () => [
                'name' => $this->location->name,
                'city' => $this->location->city,
                'country' => $this->location->country,
            ]),
        ];
    }
}
