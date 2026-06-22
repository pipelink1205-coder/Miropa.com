<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'role' => $this->role,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'created_at' => $this->created_at->toDateString(),
            'reviewer' => [
                'name' => $this->reviewer->name,
                'username' => $this->reviewer->username,
                'avatar_url' => $this->reviewer->avatar_path ? asset('storage/'.$this->reviewer->avatar_path) : null,
            ],
        ];
    }
}
