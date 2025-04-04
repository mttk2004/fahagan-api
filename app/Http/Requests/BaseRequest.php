<?php

namespace App\Http\Requests;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;

abstract class BaseRequest extends FormRequest
{
    public function failedAuthorization()
    {
        throw new AuthorizationException('Bạn không có quyền thực hiện hành động này.');
    }

    /**
     * Chuẩn bị dữ liệu trước khi validation
     * Chuyển đổi từ direct format sang JSON:API format
     */
    protected function prepareForValidation(): void
    {
        // Mặc định không làm gì, để các lớp con ghi đè nếu cần
    }
}
