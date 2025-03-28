<?php

namespace App\Http\Middleware;

use App\Utils\AuthUtils;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmployeeMiddleware
{
    /**
     * Kiểm tra nếu user là nhân viên, nếu không trả về 403 Unauthorized.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = AuthUtils::user();

        // Nếu user chưa đăng nhập => 401 Unauthorized
        if (! $user) {
            return response()->json([
                'message' => 'Truy cập bị từ chối. Vui lòng đăng nhập.',
                'status' => 401,
            ], 401);
        }

        // Nếu user không phải nhân viên => 403 Forbidden
        if ($user->is_customer) {
            return response()->json([
                'message' => 'Truy cập bị từ chối. Chỉ nhân viên mới được phép thực hiện hành động này.',
                'status' => 403,
            ], 403);
        }

        return $next($request);
    }
}
