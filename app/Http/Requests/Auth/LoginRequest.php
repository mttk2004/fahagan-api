<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Utils\AuthUtils;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rules\Password;

class LoginRequest extends BaseRequest implements HasValidationMessages
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return ! AuthUtils::user();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
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
                Password::default(),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email' => [
                'required' => 'Email là trường bắt buộc.',
                'string' => 'Email nên là một chuỗi.',
                'email' => 'Email không hợp lệ.',
                'max:50' => 'Email nên có độ dài tối đa 50.',
            ],
            'password' => [
                'required' => 'Mật khẩu là trường bắt buộc.',
                'string' => 'Mật khẩu nên là một chuỗi.',
                'password' => 'Mật khẩu nên chứa ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt.',
            ],
        ];
    }
}
