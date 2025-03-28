<?php

namespace App\Services;

use App\Interfaces\ResponseHandler;
use Illuminate\Http\JsonResponse;

class ApiResponseService implements ResponseHandler
{
    public static function success(array $data, string $message): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }

    public static function error(string $message, int $code): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'code' => $code
        ], $code);
    }

    public static function validationError(array $errors): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $errors
        ], 422);
    }
}
