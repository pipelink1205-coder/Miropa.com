<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->when($request->routeIs('*.show'), $this->description),
            'price' => (float) $this->price,
            'price_formatted' => '$'.number_format($this->price, 0, ',', '.'),
            'currency' => $this->currency,
            'is_negotiable' => $this->is_negotiable,
            'accepts_trade' => (bool) $this->accepts_trade,
            'status' => $this->status,
            'views_count' => $this->views_count,
            'favorites_count' => $this->favorites_count,
            'published_at' => $this->published_at?->diffForHumans(),
            'is_favorited' => $this->when(
                $request->user(),
                fn () => $this->favoritedByUsers->contains($request->user()->id)
            ),
            'primary_image' => $this->whenLoaded('primaryImage', fn () => asset('storage/'.$this->primaryImage->path)),
            'images' => $this->when(
                $request->routeIs('*.show'),
                fn () => $this->images->map(fn ($img) => [
                    'id' => $img->id,
                    'url' => asset('storage/'.$img->path),
                    'is_primary' => $img->is_primary,
                ])
            ),
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
                'sale_mode' => $this->category->sale_mode ?? 'marketplace',
            ]),
            'condition' => $this->whenLoaded('condition', fn () => [
                'id' => $this->condition->id,
                'name' => $this->condition->name,
            ]),
            'location' => $this->whenLoaded('location', fn () => [
                'name' => $this->location->name,
                'city' => $this->location->city,
            ]),
            'attributes' => $this->when(
                $request->routeIs('*.show'),
                fn () => $this->attributes->pluck('attribute_value', 'attribute_key')
            ),
            'seller' => $this->whenLoaded('user', fn () => array_merge([
                'id' => $this->user->id,
                'name' => $this->user->name,
                'username' => $this->user->username,
                'avatar_url' => $this->user->avatar_path ? asset('storage/'.$this->user->avatar_path) : null,
            ], ['trust' => $this->user->trustSummary()])),
        ];
    }
}
