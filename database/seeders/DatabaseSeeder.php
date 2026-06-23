<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            LocationSeeder::class,
            ConditionSeeder::class,
            BrandSeeder::class,
            CategorySeeder::class,
            FashionCategorySeeder::class,
            UniverseSeeder::class,
            ImpactFactorSeeder::class,
            UserSeeder::class,
            ListingSeeder::class,
            TransactionReviewSeeder::class,
        ]);
    }
}
