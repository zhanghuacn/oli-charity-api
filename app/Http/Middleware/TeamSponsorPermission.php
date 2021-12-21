<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;

class TeamSponsorPermission
{
    public function handle(Request $request, Closure $next)
    {
        if (!empty(auth()->user())) {
            app(PermissionRegistrar::class)->setPermissionsTeamId(auth()->user()->getTeamIdFromSponsor());
        }
        return $next($request);
    }
}
