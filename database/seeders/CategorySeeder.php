<?php
// database/seeders/CategorySeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Tênis de Corrida'],
            ['name' => 'Tênis Casual'],
            ['name' => 'Tênis de Basquete'],
            ['name' => 'Sapatos Sociais'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}