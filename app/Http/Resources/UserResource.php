<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->when($request->user()?->id === $this->id, $this->email),
            'phone' => $this->when($request->user()?->id === $this->id, $this->phone),
            'phone_verified_at' => $this->when($request->user()?->id === $this->id, $this->phone_verified_at?->toIso8601String()),
            'has_verified_phone' => $this->hasVerifiedPhone(),
            'avatar_url' => $this->avatar_path ? asset('storage/'.$this->avatar_path) : null,
            'bio' => $this->bio,
            'is_verified' => $this->is_verified,
            'verification_level' => $this->verification_level,
            'status' => $this->status,
            'last_active_at' => $this->last_active_at?->diffForHumans(),
            'created_at' => $this->created_at->toDateString(),
            'profile' => ProfileResource::make($this->whenLoaded('profile')),
        ];
    }
}
