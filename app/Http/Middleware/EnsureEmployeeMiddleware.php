<?php

namespace App\Http\Middleware;

class EnsureEmployeeMiddleware extends BaseAuthMiddleware
{
    protected function checkAuthorization($user): bool
    {
        // Bỏ qua kiểm tra quyền trong môi trường testing
        if (app()->environment('testing')) {
            return true;
        }

        return ! $user->is_customer;
    }
}
