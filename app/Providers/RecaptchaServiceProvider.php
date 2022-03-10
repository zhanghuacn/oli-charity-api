<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use ReCaptcha\ReCaptcha;

class RecaptchaServiceProvider extends ServiceProvider
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
        $this->app->bind(ReCaptcha::class, function ($app) {
            return new ReCaptcha(config('services.recaptcha.secret_key'));
        });

        $this->app->alias(ReCaptcha::class, 'recaptcha');
    }
}
