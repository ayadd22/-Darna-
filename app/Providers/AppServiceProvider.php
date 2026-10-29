<?php

namespace App\Providers;

use App\Contracts\PropertyImageServiceInterface;
use App\Contracts\PropertyServiceInterface;
use App\Services\PropertyImageService;
use App\Services\PropertyService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            PropertyServiceInterface::class,
            PropertyService::class
        );

        $this->app->bind(
            PropertyImageServiceInterface::class,
            PropertyImageService::class
        );
    }

    public function boot(): void
    {
        //
    }
}
