<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Support\SnackCatalog;
use Illuminate\Database\Seeder;

class SnackCatalogSeeder extends Seeder
{
    public function run(): void
    {
        foreach (SnackCatalog::ITEMS as $item) {
            Product::withTrashed()->firstOrCreate(['name' => $item['name']], ['price' => $item['price'], 'stock_quantity' => $item['stock']]);
        }
    }
}
