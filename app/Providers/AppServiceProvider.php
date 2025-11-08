<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Ensure Helper is always loaded
        require_once app_path('Helpers/Helper.php');
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);
    }
}
