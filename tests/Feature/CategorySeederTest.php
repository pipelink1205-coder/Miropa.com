<?php

namespace Tests\Feature;

use App\Models\Category;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ConditionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategorySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_seeder_is_idempotent(): void
    {
        $this->seed(ConditionSeeder::class);

        $this->seed(CategorySeeder::class);
        $countAfterFirstRun = Category::query()->count();

        $this->seed(CategorySeeder::class);

        $this->assertSame($countAfterFirstRun, Category::query()->count());
        $this->assertGreaterThan(0, $countAfterFirstRun);
    }
}
