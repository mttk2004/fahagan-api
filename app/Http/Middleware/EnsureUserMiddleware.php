<?php

namespace App\Http\Middleware;

class EnsureUserMiddleware extends BaseAuthMiddleware
{
    protected function checkAuthorization($user): bool
    {
        return $user !== null;
    }
}
