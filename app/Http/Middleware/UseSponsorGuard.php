<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;

class UseSponsorGuard
{
    public function handle(Request $request, Closure $next)
    {
        if (!empty(auth()->user())) {
            Auth::shouldUse('api');
            app(PermissionRegistrar::class)->setPermissionsTeamId(auth()->user()->getTeamIdFromSponsor());
        }
        return $next($request);
    }
}
