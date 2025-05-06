<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        return [
          'old_password' => 'required|string|min:8',
          'new_password' => [
            'required',
            'string',
            Password::default(),
            'different:old_password',
            'confirmed',
          ],
        ];
    }

    public function messages(): array
    {
        return [
          'old_password.required' => 'Mật khẩu cũ là trường bắt buộc.',
          'old_password.string' => 'Mật khẩu cũ nên là một chuỗi.',
          'old_password.min' => 'Mật khẩu cũ nên có ít nhất 8 ký tự.',

          'new_password.required' => 'Mật khẩu mới là trường bắt buộc.',
          'new_password.string' => 'Mật khẩu mới nên là một chuỗi.',
          'new_password.different' => 'Mật khẩu mới phải khác mật khẩu cũ.',
          'new_password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function bodyParameters(): array
    {
        return [
          'old_password' => [
            'description' => 'Mật khẩu cũ',
            'example' => 'password123',
          ],
          'new_password' => [
            'description' => 'Mật khẩu mới',
            'example' => 'password456',
          ],
        ];
    }
}
