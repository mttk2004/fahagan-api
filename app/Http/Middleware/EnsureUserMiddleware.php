<?php

namespace App\Http\Middleware;


use Auth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class EnsureUserMiddleware
{
	/**
	 * Kiểm tra nếu user chưa đăng nhập, trả về 401 Unauthorized.
	 */
	public function handle(Request $request, Closure $next): Response
	{
		$user = Auth::guard('sanctum')->user();

		// Nếu user chưa đăng nhập => 401 Unauthorized
		if (!$user) {
			return response()->json([
				'message' => 'Truy cập bị từ chối. Vui lòng đăng nhập.',
				'status' => 401,
			], 401);
		}

		return $next($request);
	}
}
