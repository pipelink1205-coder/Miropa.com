<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            'Nike', 'Adidas', 'Zara', 'H&M', 'Levi\'s', 'Pull&Bear', 'Bershka',
            'Mango', 'Forever 21', 'Puma', 'Reebok', 'Converse', 'Vans',
            'New Balance', 'Under Armour', 'Gap', 'Old Navy', 'Tommy Hilfiger',
            'Calvin Klein', 'Lacoste', 'Otra',
        ];

        foreach ($brands as $name) {
            Brand::query()->firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'is_active' => true],
            );
        }
    }
}
