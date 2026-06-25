<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Pencatatan; 
use App\Observers\PencatatanObserver;

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
        Pencatatan::observe(PencatatanObserver::class);
    }
}
