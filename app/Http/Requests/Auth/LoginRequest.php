<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Utils\AuthUtils;

class LoginRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'email',
                'max:50',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email là trường bắt buộc.',
            'email.string' => 'Email nên là một chuỗi.',
            'email.email' => 'Email không hợp lệ.',
            'email.max' => 'Email nên có độ dài tối đa 50.',

            'password.required' => 'Mật khẩu là trường bắt buộc.',
            'password.string' => 'Mật khẩu nên là một chuỗi.',
            'password.min' => 'Mật khẩu nên có ít nhất 8 ký tự.',
        ];
    }

    public function authorize(): bool
    {
        return ! AuthUtils::user();
    }

    public function bodyParameters(): array
    {
        return [
            'email' => [
                'description' => 'Email',
                'example' => 'john.doe@example.com',
            ],
            'password' => [
                'description' => 'Mật khẩu',
                'example' => 'password123',
            ],
        ];
    }
}
