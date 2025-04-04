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
        // Nếu thông báo lỗi là một đối tượng MessageBag hoặc Validator
        if ($message instanceof \Illuminate\Support\MessageBag) {
            $errors = $message->getMessages();
            $message = 'Dữ liệu không hợp lệ.';
        } elseif ($message instanceof \Illuminate\Contracts\Validation\Validator) {
            $errors = $message->errors()->getMessages();
            $message = 'Dữ liệu không hợp lệ.';
        }

        // Tiền xử lý thông báo lỗi để lấy bỏ tiền tố và loại bỏ thông báo tiếng Anh
        $processedErrors = self::processValidationErrors($errors);

        return response()->json([
            'status' => 422,
            'message' => $message,
            'errors' => $processedErrors,
        ], 422);
    }

    /**
     * Process validation errors to clean them up and make them more user-friendly
     *
     * @param array $errors The validation errors to process
     * @return array Processed validation errors
     */
    private static function processValidationErrors(array $errors): array
    {
        $processedErrors = [];

        foreach ($errors as $field => $messages) {
            $messages = is_array($messages) ? $messages : [$messages];

            foreach ($messages as $message) {
                // Check if it's a relationship error
                if (str_starts_with($field, 'data.relationships')) {
                    $matches = [];
                    preg_match('/data\.relationships\.([^.]+)/', $field, $matches);
                    if (!empty($matches[1])) {
                        $relationName = $matches[1];
                        $cleanedField = $relationName;
                        $processedErrors[$cleanedField][] = $message;
                    } else {
                        $processedErrors[$field][] = $message;
                    }
                }
                // Check if it's an attribute error
                else if (str_starts_with($field, 'data.attributes.')) {
                    $cleanedField = str_replace('data.attributes.', '', $field);

                    // Process date comparison error messages
                    if (str_contains($message, 'before or equal to') && str_contains($message, 'date')) {
                        $message = "Ngày bắt đầu phải trước hoặc bằng ngày kết thúc";
                    } else if (str_contains($message, 'after or equal to') && str_contains($message, 'date')) {
                        $message = "Ngày kết thúc phải sau hoặc bằng ngày bắt đầu";
                    } else if (str_contains($message, 'The data.attributes.') && str_contains($message, 'field')) {
                        // Extract field name from English error message
                        $fieldMatches = [];
                        if (preg_match('/The data.attributes.([^.]+) field/', $message, $fieldMatches)) {
                            $fieldName = $fieldMatches[1];

                            if (str_contains($message, 'must be a valid date')) {
                                $message = "Trường $fieldName phải là ngày hợp lệ";
                            } else if (str_contains($message, 'date format')) {
                                $message = "Trường $fieldName phải có định dạng ngày/tháng/năm";
                            }
                        }
                    }

                    $processedErrors[$cleanedField][] = $message;
                } else {
                    $cleanedField = preg_replace('/^The (.+) field.+$/', '$1', $field);
                    $processedErrors[$cleanedField][] = $message;
                }
            }
        }

        return $processedErrors;
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
