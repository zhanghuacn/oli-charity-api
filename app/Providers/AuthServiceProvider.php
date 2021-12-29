<?php

namespace App\Providers;

use App\Models\Activity;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Activity::class => \App\Policies\Charity\ActivityPolicy::class,
        Role::class => \App\Policies\Admin\RolePolicy::class,
        Permission::class => \App\Policies\Admin\PermissionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        Gate::define('check-ticket', [\App\Policies\Api\ActivityPolicy::class, 'purchase']);
        Gate::define('check-apply', [\App\Policies\Api\ActivityPolicy::class, 'apply']);
        Gate::define('check-staffs', [\App\Policies\Api\ActivityPolicy::class, 'apply']);
        Gate::define('check-group', [\App\Policies\Api\ActivityPolicy::class, 'owner']);
        Passport::routes();
        Passport::tokensCan([
            'place-app' => 'Check place app',
            'place-admin' => 'Check place admin',
            'place-charity' => 'Check place charity',
            'place-sponsor' => 'Check place sponsor',
        ]);
    }
}
