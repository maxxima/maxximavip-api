<?php

namespace App\Providers;

use App\Services\MaxxApiService;
use App\Services\MaxxApiServiceInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(MaxxApiServiceInterface::class, MaxxApiService::class);
    }
}
