<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'location_id',
        'rating_avg',
        'ratings_count',
        'sales_count',
        'purchases_count',
        'response_rate',
        'member_since',
    ];

    protected function casts(): array
    {
        return [
            'rating_avg' => 'decimal:2',
            'member_since' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
