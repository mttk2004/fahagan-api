<?php

namespace App\Http\Middleware;

class EnsureEmployeeMiddleware extends BaseAuthMiddleware
{
    protected function checkAuthorization($user): bool
    {
        return ! $user->is_customer;
    }
}
