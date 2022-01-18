<?php

namespace App\Providers;

use App\Models\Activity;
use App\Models\Permission;
use App\Models\Role;
use App\Policies\Admin\PermissionPolicy;
use App\Policies\Admin\RolePolicy;
use App\Policies\AdminPolicy;
use App\Policies\ApiPolicy;
use App\Policies\CharityPolicy;
use Illuminate\Auth\Notifications\ResetPassword;
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
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        ResetPassword::createUrlUsing(function ($user, string $token) {
            return config('app.url') . '/auth/reset-password?token=' . $token;
        });

        Passport::routes();
        Passport::tokensCan([
            'place-app' => 'Check place app',
            'place-admin' => 'Check place admin',
            'place-charity' => 'Check place charity',
            'place-sponsor' => 'Check place sponsor',
        ]);

        $this->checkApi();
        $this->checkAdmin();
        $this->checkCharity();
    }

    private function checkApi(): void
    {
        Gate::define('check-ticket', [ApiPolicy::class, 'purchase']);
        Gate::define('check-apply', [ApiPolicy::class, 'apply']);
        Gate::define('check-staff', [ApiPolicy::class, 'staff']);
        Gate::define('check-group', [ApiPolicy::class, 'group']);
    }

    private function checkAdmin(): void
    {
        Gate::define('check-admin-driver', [AdminPolicy::class, 'checkDriver']);
    }

    private function checkCharity(): void
    {
        Gate::define('check-charity-source', [CharityPolicy::class, 'checkCharity']);
    }
}
