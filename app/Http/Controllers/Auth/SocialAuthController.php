<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\HandleSocialLoginAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SocialAuthController extends Controller
{
    private const ALLOWED_PROVIDERS = ['google', 'facebook', 'microsoft', 'apple'];

    public function redirect(string $provider): RedirectResponse
    {
        $this->ensureAllowedProvider($provider);

        return Socialite::driver($this->driverName($provider))
            ->redirect();
    }

    public function callback(string $provider, HandleSocialLoginAction $action): RedirectResponse
    {
        $this->ensureAllowedProvider($provider);

        $socialUser = Socialite::driver($this->driverName($provider))->user();

        if (! $socialUser->getEmail()) {
            return redirect()->route('login')
                ->withErrors(['email' => 'No pudimos obtener tu correo de '.ucfirst($provider).'. Prueba con otro método o usa email.']);
        }

        $user = $action->execute($provider, $socialUser);

        if ($user->status !== 'active') {
            return redirect()->route('login')
                ->withErrors(['email' => 'Tu cuenta ha sido suspendida.']);
        }

        Auth::login($user, true);
        request()->session()->regenerate();

        $user->update(['last_active_at' => now()]);

        if (! $user->hasVerifiedPhone()) {
            return redirect()->route('phone.verify.notice');
        }

        return redirect()->intended(route('dashboard'));
    }

    private function driverName(string $provider): string
    {
        return $provider === 'microsoft' ? 'azure' : $provider;
    }

    private function ensureAllowedProvider(string $provider): void
    {
        if (! in_array($provider, self::ALLOWED_PROVIDERS, true)) {
            throw new NotFoundHttpException;
        }
    }
}
