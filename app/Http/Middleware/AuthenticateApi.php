<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AuthenticateApi extends Authenticate
{
    protected function authenticate($request, array $guards)
    {
        if ($this->auth->guard('api')->check()) {
            $this->auth->shouldUse('api');
        }
        throw new UnauthorizedHttpException('', 'Unauthenticated');
    }
}
