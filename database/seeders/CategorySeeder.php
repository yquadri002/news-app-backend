<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Local', 'icon' => 'map-pin', 'sort_order' => 1],
            ['name' => 'India', 'icon' => 'flag', 'sort_order' => 2],
            ['name' => 'World', 'icon' => 'globe', 'sort_order' => 3],
            ['name' => 'Politics', 'icon' => 'landmark', 'sort_order' => 4],
            ['name' => 'Technology', 'icon' => 'cpu', 'sort_order' => 5],
            ['name' => 'Business', 'icon' => 'briefcase', 'sort_order' => 6],
            ['name' => 'Sports', 'icon' => 'trophy', 'sort_order' => 7],
            ['name' => 'Entertainment', 'icon' => 'film', 'sort_order' => 8],
            ['name' => 'Health', 'icon' => 'heart', 'sort_order' => 9],
            ['name' => 'Science', 'icon' => 'flask', 'sort_order' => 10],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'icon' => $category['icon'],
                    'sort_order' => $category['sort_order'],
                    'is_enabled' => true,
                ]
            );
        }
    }
}
