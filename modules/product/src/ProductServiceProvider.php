<?php

namespace Modules\Product;

use Illuminate\Support\ServiceProvider;

final class ProductServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Infrastructure/Persistence/Migrations');
        $this->loadRoutesFrom(__DIR__.'/Api/Routes/api.php');
    }
}
