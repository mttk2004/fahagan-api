<?php

namespace App\Http\Middleware;


use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class EnsureCustomer
{
	/**
	 * Kiểm tra nếu user là khách hàng, nếu không trả về 403 Unauthorized.
	 */
	public function handle(Request $request, Closure $next): Response
	{
		$user = $request->user();

		if (!$user || !$user->is_customer) {
			return response()->json([
				'message' => 'Bị từ chối truy cập. Chỉ khách hàng mới được phép thực hiện hành động này.',
				'status' => 403,
			], 403);
		}

		return $next($request);
	}
}
