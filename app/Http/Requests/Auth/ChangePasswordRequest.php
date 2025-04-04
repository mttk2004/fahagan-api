<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Traits\HasRequestFormat;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends BaseRequest implements HasValidationMessages
{
    use HasRequestFormat;

    protected function prepareForValidation(): void
    {
        $this->convertToJsonApiFormat([
            'current_password',
            'password',
            'password_confirmation',
        ]);
    }
    public function rules(): array
    {
        return [
            'data.attributes.current_password' => 'required|string|min:8',
            'data.attributes.password' => [
                'required',
                'string',
                Password::default(),
                'different:current_password',
                'confirmed:password_confirmation',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'data.attributes.current_password.required' => 'Mật khẩu cũ là trường bắt buộc.',
            'data.attributes.current_password.string' => 'Mật khẩu cũ nên là một chuỗi.',
            'data.attributes.current_password.min' => 'Mật khẩu cũ nên có ít nhất 8 ký tự.',

            'data.attributes.password.required' => 'Mật khẩu mới là trường bắt buộc.',
            'data.attributes.password.string' => 'Mật khẩu mới nên là một chuỗi.',
            'data.attributes.password.different' => 'Mật khẩu mới phải khác mật khẩu cũ.',
            'data.attributes.password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
