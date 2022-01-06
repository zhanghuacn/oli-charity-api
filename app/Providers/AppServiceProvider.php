<?php

namespace App\Providers;

use App\Search\Search;
use Illuminate\Http\Resources\Json\JsonResource;
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
        if ($this->app->environment('local', 'staging')) {
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
