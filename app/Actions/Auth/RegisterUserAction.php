<?php

namespace App\Actions\Auth;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RegisterUserAction
{
    public function execute(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $user = User::create([
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            Profile::create([
                'user_id' => $user->id,
                'member_since' => now(),
            ]);

            return $user;
        });
    }
}
