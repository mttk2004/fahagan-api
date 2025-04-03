<?php

namespace App\Traits;

use App\Utils\ResponseUtils;
use ErrorException;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

trait HandleAuthorExceptions
{
    /**
     * Xử lý các ngoại lệ liên quan đến Author
     *
     * @param Exception $e Exception cần xử lý
     * @param array $requestData Dữ liệu từ request (đã validate)
     * @param string|int|null $authorId ID của tác giả (nếu có)
     * @param string $action Hành động đang thực hiện (tạo/cập nhật)
     * @return JsonResponse
     */
    protected function handleAuthorException(
        Exception $e,
        array $requestData,
        string|int|null $authorId = null,
        string $action = 'tạo'
    ): JsonResponse {
        // Xử lý ValidationException từ AuthorService
        if ($e instanceof ValidationException) {
            Log::info("Lỗi validation từ AuthorService khi {$action} tác giả: " . $e->getMessage());

            return ResponseUtils::badRequest(
                'Dữ liệu không hợp lệ.',
                $e->errors()
            );
        }

        // Xử lý ErrorException
        if ($e instanceof ErrorException) {
            $logData = [
                'exception' => $e,
                'request' => $requestData,
            ];

            if ($authorId) {
                $logData['author_id'] = $authorId;
            }

            Log::error("Lỗi khi {$action} tác giả: " . $e->getMessage(), $logData);

            return ResponseUtils::serverError("Đã xảy ra lỗi khi {$action} tác giả. Vui lòng thử lại sau.");
        }

        // Xử lý các Exception còn lại
        $logData = [
            'exception' => $e,
            'request' => $requestData,
        ];

        if ($authorId) {
            $logData['author_id'] = $authorId;
        }

        Log::error("Lỗi không xác định khi {$action} tác giả: " . $e->getMessage(), $logData);

        return ResponseUtils::serverError("Đã xảy ra lỗi không xác định. Vui lòng thử lại sau.");
    }
}
