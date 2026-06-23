<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Usuario de prueba fijo para desarrollo
        $demo = User::factory()->verified()->create([
            'name' => 'Diana Demo',
            'username' => 'diana_demo',
            'email' => 'demo@marketplace.test',
            'verification_level' => 'id_document',
            'is_admin' => true,
        ]);

        Profile::factory()->create([
            'user_id' => $demo->id,
            'location_id' => Location::first()?->id,
            'rating_avg' => 4.85,
            'ratings_count' => 23,
            'sales_count' => 18,
            'purchases_count' => 5,
            'member_since' => now()->subYear(),
        ]);

        // Usuario comprador/vendedor normal (no admin)
        $buyer = User::query()->firstOrCreate(
            ['email' => 'comprador@marketplace.test'],
            [
                'name' => 'María Compradora',
                'username' => 'maria_compradora',
                'password' => bcrypt('password'),
                'phone' => '3001234567',
                'phone_verified_at' => now(),
                'email_verified_at' => now(),
                'is_verified' => true,
                'verification_level' => 'phone',
                'is_admin' => false,
                'status' => 'active',
            ],
        );

        Profile::query()->firstOrCreate(
            ['user_id' => $buyer->id],
            [
                'location_id' => Location::first()?->id,
                'rating_avg' => 4.2,
                'ratings_count' => 3,
                'sales_count' => 2,
                'purchases_count' => 7,
                'member_since' => now()->subMonths(3),
            ],
        );

        // 9 usuarios aleatorios con perfil
        User::factory(9)->create()->each(function (User $user) {
            Profile::factory()->create(['user_id' => $user->id]);
        });
    }
}
