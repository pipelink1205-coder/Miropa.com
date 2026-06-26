<?php

namespace App\Providers;

use App\Models\Conversation;
use App\Models\TradeOffer;
use App\Policies\ConversationPolicy;
use App\Policies\TradeOfferPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Apple\Provider as AppleProvider;
use SocialiteProviders\Azure\AzureExtendSocialite;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('admin', fn ($user) => $user->is_admin && $user->status === 'active');
        Gate::policy(Conversation::class, ConversationPolicy::class);
        Gate::policy(TradeOffer::class, TradeOfferPolicy::class);

        Event::listen(SocialiteWasCalled::class, [AzureExtendSocialite::class, 'handle']);

        Event::listen(function (SocialiteWasCalled $event): void {
            $event->extendSocialite('apple', AppleProvider::class);
        });
    }
}
