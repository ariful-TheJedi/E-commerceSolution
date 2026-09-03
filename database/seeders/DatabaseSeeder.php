<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Product\Infrastructure\Persistence\Factories\ProductCatalogFactory;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $seedProductCatalog = app()->environment(['local', 'staging']) || (bool) env('PRODUCT_SEED', false);

        if ($seedProductCatalog && config('database.connections.product.driver') === 'pgsql') {
            app(ProductCatalogFactory::class)->seed();
        }
    }
}
