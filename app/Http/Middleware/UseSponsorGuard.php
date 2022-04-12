<?php

namespace App\Http\Middleware;

use App\Models\Sponsor;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;

class UseSponsorGuard
{
    public function handle(Request $request, Closure $next)
    {
        if (!empty(Auth::user())) {
            Auth::shouldUse(Sponsor::GUARD_NAME);
            app(PermissionRegistrar::class)->setPermissionsTeamId(Auth::user()->getTeamIdFromSponsor());
        }
        return $next($request);
    }
}
