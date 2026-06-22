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
            $account = SocialAccount::where('provider', $provider)
                ->where('provider_id', $socialUser->getId())
                ->first();

            if ($account) {
                $account->update([
                    'email' => $socialUser->getEmail(),
                    'avatar_url' => $socialUser->getAvatar(),
                ]);

                return $account->user;
            }

            $user = User::where('email', $socialUser->getEmail())->first();

            if (! $user) {
                $user = $this->createUserFromSocial($socialUser);
            } else {
                $this->markEmailVerified($user);
            }

            SocialAccount::create([
                'user_id' => $user->id,
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'email' => $socialUser->getEmail(),
                'avatar_url' => $socialUser->getAvatar(),
            ]);

            if ($socialUser->getAvatar() && ! $user->avatar_path) {
                $user->update(['avatar_path' => $socialUser->getAvatar()]);
            }

            return $user->fresh();
        });
    }

    private function createUserFromSocial(SocialiteUser $socialUser): User
    {
        $baseUsername = Str::slug(Str::before($socialUser->getEmail() ?? $socialUser->getName(), '@'), '_');
        $username = $this->uniqueUsername($baseUsername ?: 'usuario');

        $user = User::create([
            'name' => $socialUser->getName() ?: $username,
            'username' => $username,
            'email' => $socialUser->getEmail(),
            'password' => null,
            'email_verified_at' => now(),
            'avatar_path' => $socialUser->getAvatar(),
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
}
