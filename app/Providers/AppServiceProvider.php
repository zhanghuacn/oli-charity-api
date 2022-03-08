<?php

namespace App\Providers;

use App\Search\Search;
use Illuminate\Auth\Recaller;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;
use ReCaptcha\ReCaptcha;
use Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment('local')) {
            $this->app->register('Laravel\Telescope\TelescopeApplicationServiceProvider');
            $this->app->register('Laravel\Telescope\TelescopeServiceProvider');
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        JsonResource::withoutWrapping();
        Search::bootSearchable();
    }
}
