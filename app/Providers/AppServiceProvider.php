<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Forcer l'encodage UTF-8 pour éviter les problèmes de caractères
        DB::getEventDispatcher()->listen('connection.*', function ($event, $data) {
            if (isset($data[0]) && $data[0] instanceof \PDO) {
                $data[0]->exec("SET NAMES 'utf8mb4'");
                $data[0]->exec("SET CHARACTER SET utf8mb4");
            }
        });
    }
}
