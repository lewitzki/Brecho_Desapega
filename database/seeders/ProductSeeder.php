<?php
// database/seeders/ProductSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $products = [
            [
                'name' => 'Air Max Runner Pro',
                'price' => 399.90,
                'stock' => 25,
                'category_id' => 1,
                'description' => 'Tênis de corrida de alta performance...',
                'image' => 'https://images.unsplash.com/photo-1597892657493-6847b9640bac',
                'sizes' => [38, 39, 40, 41, 42, 43, 44]
            ],
            // Adicionar mais produtos...
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}