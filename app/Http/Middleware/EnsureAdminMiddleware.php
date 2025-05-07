<?php

namespace App\Http\Middleware;

class EnsureAdminMiddleware extends BaseAuthMiddleware
{
    protected function checkAuthorization($user): bool
    {
        return ! $user->is_customer && $user->hasRole('Admin');
    }
}
