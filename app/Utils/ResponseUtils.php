<?php

namespace App\Utils;

use Illuminate\Http\JsonResponse;

class ResponseUtils
{
    /**
     * Trả về phản hồi 200 OK
     */
    public static function success(array $data = [], string $message = 'Thành công.'): JsonResponse
    {
        return response()->json([
            'status' => 200,
            'message' => $message,
            'data' => $data,
        ]);
    }

    /**
     * Trả về phản hồi 201 Created
     */
    public static function created(array $data = [], string $message = 'Tạo mới thành công.'):
    JsonResponse
    {
        return response()->json([
            'status' => 201,
            'message' => $message,
            'data' => $data,
        ], 201);
    }

    /**
     * Trả về phản hồi 204 No Content
     */
    public static function noContent(string $message = 'Không có nội dung.'): JsonResponse
    {
        return response()->json([
            'status' => 204,
            'message' => $message,
        ], 204);
    }

    /**
     * Trả về phản hồi 400 Bad Request
     */
    public static function badRequest(
        string $message = 'Yêu cầu không hợp lệ.',
        array $errors = [],
    ):
    JsonResponse {
        return response()->json([
            'status' => 400,
            'message' => $message,
            'errors' => $errors,
        ], 400);
    }

    /**
     * Trả về phản hồi 401 Unauthorized
     */
    public static function unauthorized(string $message = 'Bạn không có quyền truy cập.'):
    JsonResponse
    {
        return response()->json([
            'status' => 401,
            'message' => $message,
        ], 401);
    }

    /**
     * Trả về phản hồi 403 Forbidden
     */
    public static function forbidden(
        string $message = 'Bạn không được phép thực hiện hành động này.',
    ): JsonResponse {
        return response()->json([
            'status' => 403,
            'message' => $message,
        ], 403);
    }

    /**
     * Trả về phản hồi 404 Not Found
     */
    public static function notFound(string $message = 'Không tìm thấy dữ liệu.'): JsonResponse
    {
        return response()->json([
            'status' => 404,
            'message' => $message,
        ], 404);
    }

    /**
     * Trả về phản hồi 422 Unprocessable Entity (dành cho validation error)
     */
    public static function validationError(
        string $message = 'Dữ liệu không hợp lệ.',
        array $errors = [],
    ): JsonResponse {
        return response()->json([
            'status' => 422,
            'message' => $message,
            'errors' => $errors,
        ], 422);
    }

    /**
     * Trả về phản hồi 429 Too Many Requests
     */
    public static function tooManyRequests(
        string $message = 'Bạn đã gửi quá nhiều yêu cầu, vui lòng thử lại sau.',
    ): JsonResponse {
        return response()->json([
            'status' => 429,
            'message' => $message,
        ], 429);
    }

    /**
     * Trả về phản hồi 500 Internal Server Error
     */
    public static function serverError(string $message = 'Lỗi máy chủ, vui lòng thử lại sau.'):
    JsonResponse
    {
        return response()->json([
            'status' => 500,
            'message' => $message,
        ], 500);
    }

    /**
     * Trả về phản hồi 503 Service Unavailable
     */
    public static function serviceUnavailable(
        string $message = 'Dịch vụ hiện không khả dụng, vui lòng thử lại sau.',
    ): JsonResponse {
        return response()->json([
            'status' => 503,
            'message' => $message,
        ], 503);
    }

    /**
     * Trả về phản hồi lỗi chung
     * Mặc định sẽ trả về lỗi 500 (Internal Server Error)
     */
    public static function error(
        string $message = 'Đã xảy ra lỗi.',
        int $statusCode = 500,
        array $errors = [],
    ): JsonResponse {
        return response()->json([
            'status' => $statusCode,
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }
}
