<?php

namespace App\Http\Middleware;

use App\Models\Charity;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;

class UseCharityGuard
{
    public function handle(Request $request, Closure $next)
    {
        if (!empty(Auth::user())) {
            Auth::shouldUse(Charity::GUARD_NAME);
            app(PermissionRegistrar::class)->setPermissionsTeamId(Auth::user()->getTeamIdFromCharity());
        }
        return $next($request);
    }
}
