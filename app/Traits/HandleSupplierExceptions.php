<?php

namespace App\Traits;

use App\Utils\ResponseUtils;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use PDOException;

trait HandleSupplierExceptions
{
    /**
     * Xử lý các exception liên quan đến nhà cung cấp
     *
     * @param Exception $exception
     * @param array $requestData
     * @param string|int|null $id
     * @param string $action Hành động đang thực hiện (tạo, cập nhật, xóa, khôi phục)
     * @return JsonResponse
     */
    protected function handleSupplierException(
        Exception $exception,
        array $requestData = [],
        string|int|null $id = null,
        string $action = 'xử lý'
    ): JsonResponse {
        Log::error("Supplier {$action} failed", [
            'exception' => $exception->getMessage(),
            'request_data' => $requestData,
            'id' => $id,
        ]);

        if ($exception instanceof ValidationException) {
            return ResponseUtils::validationError(
                'Dữ liệu không hợp lệ. Vui lòng kiểm tra lại.',
                $exception->errors()
            );
        }

        if ($exception instanceof ModelNotFoundException) {
            return ResponseUtils::notFound('Không tìm thấy nhà cung cấp.');
        }

        if ($exception instanceof PDOException) {
            if (str_contains($exception->getMessage(), 'Duplicate entry')) {
                return ResponseUtils::validationError('Nhà cung cấp đã tồn tại.');
            }
        }

        // Xử lý các exception khác
        return ResponseUtils::serverError("Không thể {$action} nhà cung cấp. Vui lòng thử lại sau.");
    }
}
