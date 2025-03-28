<?php

namespace App\Http\Middleware;

class EnsureCustomerMiddleware extends BaseAuthMiddleware
{
    protected function checkAuthorization($user): bool
    {
        return $user->is_customer;
    }
}
