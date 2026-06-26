<?php

namespace Tests\Feature;

use Database\Seeders\CategorySeeder;
use Database\Seeders\ConditionSeeder;
use Database\Seeders\FashionCategorySeeder;
use Database\Seeders\LocationSeeder;
use Database\Seeders\TradeTestUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TradeDemoUsersSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_trade_demo_users_can_login_with_password(): void
    {
        $this->seed([
            LocationSeeder::class,
            ConditionSeeder::class,
            CategorySeeder::class,
            FashionCategorySeeder::class,
        ]);

        $this->seed(TradeTestUsersSeeder::class);

        $this->post('/login', [
            'email' => 'bruno.trueque@marketplace.test',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->post('/logout');

        $this->post('/login', [
            'email' => 'ana.trueque@marketplace.test',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));
    }
}
