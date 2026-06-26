<?php

namespace App\Actions\Auth;

use App\Models\Profile;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class HandleSocialLoginAction
{
    public function execute(string $provider, SocialiteUser $socialUser): User
    {
        return DB::transaction(function () use ($provider, $socialUser): User {
            $email = $this->resolveEmail($socialUser);
            $avatarUrl = $this->resolveAvatarUrl($socialUser);

            $account = SocialAccount::where('provider', $provider)
                ->where('provider_id', $socialUser->getId())
                ->first();

            if ($account) {
                $account->update([
                    'email' => $email,
                    'avatar_url' => $avatarUrl,
                ]);

                return $account->user;
            }

            $user = User::where('email', $email)->first();

            if (! $user) {
                $user = $this->createUserFromSocial($socialUser);
            } else {
                $this->markEmailVerified($user);
            }

            SocialAccount::create([
                'user_id' => $user->id,
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'email' => $email,
                'avatar_url' => $avatarUrl,
            ]);

            if ($avatarUrl && ! $user->avatar_path) {
                $user->update(['avatar_path' => $avatarUrl]);
            }

            return $user->fresh();
        });
    }

    private function createUserFromSocial(SocialiteUser $socialUser): User
    {
        $email = $this->resolveEmail($socialUser);
        $avatarUrl = $this->resolveAvatarUrl($socialUser);
        $baseUsername = Str::slug(Str::before($email ?? $socialUser->getName(), '@'), '_');
        $username = $this->uniqueUsername($baseUsername ?: 'usuario');

        $user = User::create([
            'name' => $socialUser->getName() ?: $username,
            'username' => $username,
            'email' => $email,
            'password' => null,
            'email_verified_at' => now(),
            'avatar_path' => $avatarUrl,
        ]);

        Profile::create([
            'user_id' => $user->id,
            'member_since' => now(),
        ]);

        return $user;
    }

    private function markEmailVerified(User $user): void
    {
        if (! $user->hasVerifiedEmail()) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }
    }

    private function uniqueUsername(string $base): string
    {
        $username = Str::limit($base, 25, '');
        $suffix = 0;

        while (User::where('username', $username)->exists()) {
            $suffix++;
            $username = Str::limit($base, 20, '').'_'.$suffix;
        }

        return $username;
    }

    private function resolveEmail(SocialiteUser $socialUser): ?string
    {
        $email = $socialUser->getEmail();

        if (filled($email)) {
            return $email;
        }

        $raw = $socialUser->getRaw();

        if (is_array($raw)) {
            foreach (['mail', 'userPrincipalName', 'email'] as $key) {
                if (! empty($raw[$key]) && str_contains((string) $raw[$key], '@')) {
                    return (string) $raw[$key];
                }
            }
        }

        return null;
    }

    private function resolveAvatarUrl(SocialiteUser $socialUser): ?string
    {
        $avatar = $socialUser->getAvatar();

        return filled($avatar) ? Str::limit($avatar, 255, '') : null;
    }
}
