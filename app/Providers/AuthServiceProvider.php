<?php

namespace App\Providers;

use App\Models\Activity;
use App\Models\Permission;
use App\Models\Role;
use App\Policies\ActivityPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Activity::class => ActivityPolicy::class,
        Role::class => RolePolicy::class,
        Permission::class => PermissionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();
        Passport::tokensCan([
            'place-app' => 'Check place app',
            'place-admin' => 'Check place admin',
            'place-charity' => 'Check place charity',
            'place-sponsor' => 'Check place sponsor',
        ]);
    }
}
