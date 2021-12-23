<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;

class UseCharityGuard
{
    public function handle(Request $request, Closure $next)
    {
        if (!empty(auth()->user())) {
            Auth::shouldUse('charity');
            app(PermissionRegistrar::class)->setPermissionsTeamId(auth()->user()->getTeamIdFromCharity());
        }
        return $next($request);
    }
}
