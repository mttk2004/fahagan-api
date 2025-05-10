<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Utils\AuthUtils;
use Illuminate\Http\Request;

class UserUpdateRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'string', 'max:30'],
            'last_name' => ['sometimes', 'string', 'max:30'],
            'phone' => [
                'sometimes',
                'string',
                'regex:/^0[35789][0-9]{8}$/',
                'unique:users,phone',
            ],
            'email' => [
                'sometimes',
                'string',
                'lowercase',
                'email',
                'max:50',
                'unique:users,email',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.string' => 'Tên nên là một chuỗi.',
            'first_name.max' => 'Tên nên có độ dài tối đa 30.',

            'last_name.string' => 'Họ nên là một chuỗi.',
            'last_name.max' => 'Họ nên có độ dài tối đa 30.',

            'phone.string' => 'Số điện thoại nên là một chuỗi.',
            'phone.regex' => 'Số điện thoại không hợp lệ.',
            'phone.unique' => 'Số điện thoại đã được sử dụng.',

            'email.string' => 'Email nên là một chuỗi.',
            'email.lowercase' => 'Email nên viết thường.',
            'email.email' => 'Email không hợp lệ.',
            'email.max' => 'Email nên có độ dài tối đa 50.',
            'email.unique' => 'Email đã được sử dụng.',
        ];
    }

    public function authorize(Request $request): bool
    {
        $user = AuthUtils::user();

        // Nếu route là customer.profile.update thì luôn cho phép người dùng cập nhật chính mình
        if ($request->route()->getName() === 'customer.profile.update') {
            return true;
        }

        // Với các route khác (AdminCustomerController) thì kiểm tra quyền edit_users hoặc là chính người dùng đó
        return AuthUtils::userCan('edit_users') || $user->id == $request->route('user');
    }

    public function bodyParameters(): array
    {
        return [
            'first_name' => [
                'description' => 'Tên',
                'example' => 'John',
            ],
            'last_name' => [
                'description' => 'Họ',
                'example' => 'Doe',
            ],
            'phone' => [
                'description' => 'Số điện thoại',
                'example' => '0909090909',
            ],
            'email' => [
                'description' => 'Email',
                'example' => 'john.doe@example.com',
            ],
        ];
    }
}
