<?php

namespace App\Providers;

use App\Listeners\PruneOldTokens;
use App\Listeners\RevokeOldTokens;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Laravel\Passport\Events\AccessTokenCreated;
use Laravel\Passport\Events\RefreshTokenCreated;
use SocialiteProviders\Apple\AppleExtendSocialite;
use SocialiteProviders\Manager\SocialiteWasCalled;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        AccessTokenCreated::class => [
            RevokeOldTokens::class,
        ],
        RefreshTokenCreated::class => [
            PruneOldTokens::class,
        ],
        SocialiteWasCalled::class => [
            AppleExtendSocialite::class.'@handle',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
    }
}
