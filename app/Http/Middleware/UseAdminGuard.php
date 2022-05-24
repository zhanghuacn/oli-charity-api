<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;

class UseAdminGuard
{
    public function handle(Request $request, Closure $next)
    {
        if (!empty(Auth::user())) {
            Auth::shouldUse(Admin::GUARD_NAME);
            app(PermissionRegistrar::class)->setPermissionsTeamId(0);
        }
        return $next($request);
    }
}
