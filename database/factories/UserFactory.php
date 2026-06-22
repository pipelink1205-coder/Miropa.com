<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'phone_verified_at' => now(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'avatar_path' => null,
            'bio' => fake()->optional()->sentence(),
            'is_verified' => true,
            'verification_level' => 'phone',
            'status' => 'active',
            'last_active_at' => fake()->dateTimeBetween('-30 days'),
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }

    public function withoutPhoneVerification(): static
    {
        return $this->state(fn () => [
            'phone_verified_at' => null,
            'is_verified' => false,
            'verification_level' => 'email',
        ]);
    }

    public function phoneVerified(): static
    {
        return $this->state(fn () => [
            'phone_verified_at' => now(),
            'is_verified' => true,
            'verification_level' => 'phone',
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn () => [
            'is_verified' => true,
            'verification_level' => 'id_document',
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn () => ['status' => 'suspended']);
    }
}
