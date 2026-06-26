<?php

namespace App\Http\Middleware;

use App\Models\TradeOffer;
use App\Models\Universe;
use App\Services\TradeEligibilityService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $logoPath = config('brand.logo');

        return array_merge(parent::share($request), [
            'brand' => [
                'name' => config('brand.name'),
                'domain' => config('brand.domain'),
                'tagline' => config('brand.tagline'),
                'logo_url' => $logoPath && file_exists(public_path($logoPath))
                    ? asset($logoPath)
                    : null,
            ],
            'auth' => [
                'user' => $request->user() ? array_merge([
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'username' => $request->user()->username,
                    'email' => $request->user()->email,
                    'is_verified' => $request->user()->is_verified,
                    'avatar_path' => $request->user()->avatar_path,
                ], ['trust' => $request->user()->trustSummary()], [
                    'trade_eligible' => app(TradeEligibilityService::class)->isEligible($request->user()),
                    'trade_ineligible_reason' => app(TradeEligibilityService::class)->failureReason($request->user()),
                ]) : null,
            ],
            'unread_messages_count' => $request->user()?->unreadMessagesCount() ?? 0,
            'pending_trade_offers_count' => fn () => $this->pendingTradeOffersCount($request),
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'dev_code' => $request->session()->get('dev_code'),
            ],
            'features' => [
                'checkout_enabled' => (bool) config('marketplace.checkout_enabled'),
                'trade_enabled' => (bool) config('marketplace.trade.enabled', true),
                'social_auth' => [
                    'google' => filled(config('services.google.client_id')),
                    'facebook' => filled(config('services.facebook.client_id')),
                ],
            ],
            'fashionUniverses' => fn () => Universe::query()
                ->where('is_active', true)
                ->orderBy('position')
                ->get(['id', 'name', 'slug', 'accent_color', 'description']),
        ]);
    }

    private function pendingTradeOffersCount(Request $request): int
    {
        $user = $request->user();

        if ($user === null) {
            return 0;
        }

        return TradeOffer::query()
            ->where('status', 'pending')
            ->where(function ($query) use ($user): void {
                $query->where('proposer_id', $user->id)
                    ->orWhereHas('targetListing', fn ($q) => $q->where('user_id', $user->id));
            })
            ->count();
    }
}
