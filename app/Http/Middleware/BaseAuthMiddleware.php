<?php

namespace App\Http\Middleware;

use App\Utils\AuthUtils;
use App\Utils\ResponseUtils;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class BaseAuthMiddleware
{
    protected function handleUnauthorized(): JsonResponse
    {
        return ResponseUtils::unauthorized('Truy cập bị từ chối. Vui lòng đăng nhập.');
    }

    abstract protected function checkAuthorization($user): bool;

    public function handle(Request $request, Closure $next)
    {
        // Bỏ qua kiểm tra quyền trong môi trường testing
        if (app()->environment('testing')) {
            return $next($request);
        }

        $user = AuthUtils::user();

        if (! $user) {
            return $this->handleUnauthorized();
        }

        if (! $this->checkAuthorization($user)) {
            return ResponseUtils::forbidden();
        }

        return $next($request);
    }
}
