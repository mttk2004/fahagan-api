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
     * Kiểm tra xem request có chứa khóa và giá trị không
     *
     * @param string $key
     * @return bool
     */
    protected function hasValue(string $key): bool
    {
        return array_key_exists($key, $this->all()) && !empty($this->all()[$key]);
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
