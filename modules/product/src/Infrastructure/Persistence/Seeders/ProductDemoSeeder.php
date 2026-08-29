<?php

namespace Modules\Product\Infrastructure\Persistence\Seeders;

use Illuminate\Database\Seeder;
use Modules\Product\Infrastructure\Persistence\Models\Product;

/**
 * Demo seed — requires product schema + products migration.
 * For API demo without DB, ProductApiImpl uses ProductFactory::make().
 */
final class ProductDemoSeeder extends Seeder
{
    public function run(): void
    {
        Product::factory()->active()->count(6)->create();
        Product::factory()->draft()->count(2)->create();
    }
}
