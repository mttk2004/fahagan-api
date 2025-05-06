<?php

namespace App\Traits;

use App\Utils\ResponseUtils;
use Illuminate\Http\JsonResponse;

trait HandleValidation
{
    /**
     * Kiểm tra xem dữ liệu cập nhật có rỗng không
     */
    protected function isEmptyUpdateData(?array $validatedData): bool
    {
        return empty($validatedData ?? []);
    }

    /**
     * Kiểm tra và trả về response lỗi nếu dữ liệu cập nhật rỗng
     */
    protected function validateUpdateData(?array $validatedData): ?JsonResponse
    {
        if ($this->isEmptyUpdateData($validatedData)) {
            return ResponseUtils::badRequest('Không có dữ liệu nào để cập nhật.');
        }

        return null;
    }
}
