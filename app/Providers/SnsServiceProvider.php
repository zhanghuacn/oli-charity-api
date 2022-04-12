<?php

namespace App\Providers;

use Aws\Sns\SnsClient;
use Illuminate\Support\ServiceProvider;

class SnsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind(SnsClient::class, function ($app) {
            return new SnsClient(['region' => config('aws.region'), 'version' => config('aws.version')]);
        });

        $this->app->alias(SnsClient::class, 'sns');
    }
}
