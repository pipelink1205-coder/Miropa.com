<?php

namespace Database\Seeders;

use App\Support\FashionCategoryTreeSync;
use Illuminate\Database\Seeder;

class FashionCategorySeeder extends Seeder
{
    public function run(): void
    {
        FashionCategoryTreeSync::syncAll();
    }
}
