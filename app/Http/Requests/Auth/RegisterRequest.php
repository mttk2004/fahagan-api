<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Traits\HasRequestFormat;
use App\Utils\AuthUtils;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends BaseRequest implements HasValidationMessages
{
    use HasRequestFormat;

    protected function prepareForValidation(): void
    {
        $this->convertToJsonApiFormat(['first_name', 'last_name', 'phone', 'email', 'password', 'password_confirmation']);
    }

    public function rules(): array
    {
        return [
            'data.attributes.first_name' => ['required', 'string', 'max:30'],
            'data.attributes.last_name' => ['required', 'string', 'max:30'],
            'data.attributes.phone' => [
                'required',
                'string',
                'regex:/^0[35789][0-9]{8}$/',
                'unique:users,phone',
            ],
            'data.attributes.email' => [
                'required',
                'string',
                'email',
                'max:50',
                'unique:users,email',
            ],
            'data.attributes.password' => [
                'required',
                'string',
                'confirmed',
                Password::default(),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'data.attributes.first_name.required' => 'Tên là trường bắt buộc.',
            'data.attributes.first_name.string' => 'Tên nên là một chuỗi.',
            'data.attributes.first_name.max' => 'Tên nên có độ dài tối đa 30.',

            'data.attributes.last_name.required' => 'Họ là trường bắt buộc.',
            'data.attributes.last_name.string' => 'Họ nên là một chuỗi.',
            'data.attributes.last_name.max' => 'Họ nên có độ dài tối đa 30.',

            'data.attributes.phone.required' => 'Số điện thoại là trường bắt buộc.',
            'data.attributes.phone.string' => 'Số điện thoại nên là một chuỗi.',
            'data.attributes.phone.regex' => 'Số điện thoại không hợp lệ.',
            'data.attributes.phone.unique' => 'Số điện thoại đã được sử dụng.',

            'data.attributes.email.required' => 'Email là trường bắt buộc.',
            'data.attributes.email.string' => 'Email nên là một chuỗi.',
            'data.attributes.email.email' => 'Email không hợp lệ.',
            'data.attributes.email.max' => 'Email nên có độ dài tối đa 50.',
            'data.attributes.email.unique' => 'Email đã được sử dụng.',

            'data.attributes.password.required' => 'Mật khẩu là trường bắt buộc.',
            'data.attributes.password.string' => 'Mật khẩu nên là một chuỗi.',
            'data.attributes.password.confirmed' => 'Mật khẩu không khớp.',
            'data.attributes.password.password' => 'Mật khẩu nên chứa ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt.',
        ];
    }

    public function authorize(): bool
    {
        return ! AuthUtils::user();
    }
}
