<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::updateOrCreate(
            ['name' => 'Laptop Pro'],
            [
                'description' => 'Laptop profesional de alto rendimiento.',
                'price' => 899.99,
                'stock' => 10,
                'is_active' => true,
            ]
        );

        Product::updateOrCreate(
            ['name' => 'Mouse Inalámbrico'],
            [
                'description' => 'Mouse inalámbrico ergonómico.',
                'price' => 29.99,
                'stock' => 50,
                'is_active' => true,
            ]
        );

        Product::updateOrCreate(
            ['name' => 'Teclado Mecánico'],
            [
                'description' => 'Teclado mecánico para trabajo y gaming.',
                'price' => 79.99,
                'stock' => 25,
                'is_active' => true,
            ]
        );

        Product::updateOrCreate(
            ['name' => 'Monitor 24 Pulgadas'],
            [
                'description' => 'Monitor Full HD de 24 pulgadas.',
                'price' => 179.99,
                'stock' => 15,
                'is_active' => true,
            ]
        );
    }
}