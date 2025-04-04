<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Traits\HasApiJsonValidation;
use App\Traits\HasRequestFormat;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends BaseRequest implements HasValidationMessages
{
    use HasApiJsonValidation;
    use HasRequestFormat;

    /**
     * Chuẩn bị dữ liệu trước khi validation
     */
    protected function prepareForValidation(): void
    {
        // Chuyển đổi từ direct format sang JSON:API format
        $this->convertToJsonApiFormat([
            'current_password',
            'password',
            'password_confirmation',
        ]);
    }

    public function rules(): array
    {
        return $this->mapAttributesRules([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string', 'min:8'],
        ]);
    }

    public function messages(): array
    {
        return [
            'data.attributes.old_password.required' => 'Mật khẩu cũ là trường bắt buộc.',
            'data.attributes.old_password.string' => 'Mật khẩu cũ nên là một chuỗi.',
            'data.attributes.old_password.min' => 'Mật khẩu cũ nên có ít nhất 8 ký tự.',
            'data.attributes.new_password.required' => 'Mật khẩu mới là trường bắt buộc.',
            'data.attributes.new_password.string' => 'Mật khẩu mới nên là một chuỗi.',
            'data.attributes.new_password.different' => 'Mật khẩu mới phải khác mật khẩu cũ.',
            'data.attributes.new_password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
            'data.attributes.new_password_confirmation.required' => 'Xác nhận mật khẩu mới là trường bắt buộc.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
