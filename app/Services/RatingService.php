<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\Review;

class RatingService
{
    public function recalculate(int $userId): void
    {
        $stats = Review::where('reviewee_id', $userId)
            ->selectRaw('COUNT(*) as count, AVG(rating) as avg_rating')
            ->first();

        Profile::where('user_id', $userId)->update([
            'rating_avg' => round((float) $stats->avg_rating, 2),
            'ratings_count' => (int) $stats->count,
        ]);
    }
}
