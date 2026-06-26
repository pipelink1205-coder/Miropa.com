<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TradeOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'proposer_id',
        'target_listing_id',
        'offered_listing_id',
        'conversation_id',
        'status',
        'message',
        'responded_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'responded_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function proposer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposer_id');
    }

    public function targetListing(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'target_listing_id');
    }

    public function offeredListing(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'offered_listing_id');
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}
