<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Traits\HasRequestFormat;
use App\Utils\AuthUtils;
use Illuminate\Validation\Rules\Password;

class LoginRequest extends BaseRequest implements HasValidationMessages
{
    use HasRequestFormat;

    protected function prepareForValidation(): void
    {
        $this->convertToJsonApiFormat(['email', 'password']);
    }

    public function rules(): array
    {
        return [
            'data.attributes.email' => [
                'required',
                'string',
                'email',
                'max:50',
            ],
            'data.attributes.password' => [
                'required',
                'string',
                Password::default(),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'data.attributes.email.required' => 'Email là trường bắt buộc.',
            'data.attributes.email.string' => 'Email nên là một chuỗi.',
            'data.attributes.email.email' => 'Email không hợp lệ.',
            'data.attributes.email.max' => 'Email nên có độ dài tối đa 50.',

            'data.attributes.password.required' => 'Mật khẩu là trường bắt buộc.',
            'data.attributes.password.string' => 'Mật khẩu nên là một chuỗi.',
            'data.attributes.password.password' => 'Mật khẩu nên chứa ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt.',
        ];
    }

    public function authorize(): bool
    {
        return ! AuthUtils::user();
    }
}
