<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;

class UseAdminGuard
{
    public function handle(Request $request, Closure $next)
    {
        Auth::shouldUse('admin');
        app(PermissionRegistrar::class)->setPermissionsTeamId(0);
        return $next($request);
    }
}
