<?php

namespace App\Services;

use App\Models\User;
use Carbon\CarbonInterface;

class TradeEligibilityService
{
    public function isEligible(User $user): bool
    {
        return $this->failureReason($user) === null;
    }

    public function failureReason(User $user): ?string
    {
        if (! config('marketplace.trade.enabled', true)) {
            return 'El trueque no está disponible en este momento.';
        }

        if ($user->status !== 'active') {
            return 'Tu cuenta no está activa.';
        }

        if (config('marketplace.trade.require_identity', true) && ! $user->hasVerifiedIdentity()) {
            return 'Verifica tu identidad con documento para usar trueque.';
        }

        $memberSince = $user->profile?->member_since ?? $user->created_at;
        $minAgeDays = (int) config('marketplace.trade.min_account_age_days', 30);

        if ($memberSince instanceof CarbonInterface && $memberSince->diffInDays(now()) < $minAgeDays) {
            return "Tu cuenta debe tener al menos {$minAgeDays} días para usar trueque.";
        }

        $profile = $user->profile;

        if ($profile === null) {
            return 'Completa tu perfil y acumula historial en la plataforma para usar trueque.';
        }

        $minRatings = (int) config('marketplace.trade.min_ratings_count', 3);
        $minRatingAvg = (float) config('marketplace.trade.min_rating_avg', 4.0);
        $minTransactions = (int) config('marketplace.trade.min_completed_transactions', 1);

        $hasRatingPath = $profile->ratings_count >= $minRatings
            && (float) $profile->rating_avg >= $minRatingAvg;

        $hasActivityPath = ($profile->sales_count + $profile->purchases_count) >= $minTransactions;

        if (! $hasRatingPath && ! $hasActivityPath) {
            return 'Necesitas al menos una venta o compra completada, o reseñas con buena calificación, para usar trueque.';
        }

        return null;
    }
}
