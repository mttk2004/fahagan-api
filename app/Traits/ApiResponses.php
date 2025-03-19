<?php

namespace App\Traits;


use Illuminate\Http\JsonResponse;


trait ApiResponses
{
	protected function success(
		string $message,
		array $data = [],
		int $statusCode = 200,
	): JsonResponse {
		return response()->json(
			[
				'data' => $data,
				'message' => $message,
				'status' => $statusCode,
			],
			$statusCode
		);
	}

	protected function error(string $message, int $statusCode): JsonResponse
	{
		return response()->json(
			[
				'message' => $message,
				'status' => $statusCode,
			],
			$statusCode
		);
	}

	protected function ok(string $message, array $data = []): JsonResponse
	{
		return $this->success($message, $data);
	}

	protected function forbidden(): JsonResponse
	{
		return $this->error('Bạn không có quyền thực hiện hành động này.', 403);
	}
}
