<?php

namespace App\Http\Requests\V1;


use App\Http\Requests\BaseRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;


class UserUpdateRequest extends BaseRequest
{
	public function rules(): array
	{
		return [
			'data.attributes.first_name' => ['sometimes', 'string', 'max:30'],
			'data.attributes.last_name' => ['sometimes', 'string', 'max:30'],
			'data.attributes.phone' => [
				'sometimes',
				'string',
				'regex:/^0[35789][0-9]{8}$/',
				'unique:' . User::class,
			],
			'data.attributes.email' => [
				'sometimes',
				'string',
				'lowercase',
				'email',
				'max:50',
				'unique:' . User::class,
			],
			'data.attributes.password' => ['sometimes', 'confirmed', Password::default()],
		];
	}

	public function messages(): array
	{
		return [
			'data.attributes.first_name' => [
				'string' => 'Tên nên là một chuỗi.',
				'max:30' => 'Tên nên có độ dài tối đa 30.',
			],
			'data.attributes.last_name' => [
				'string' => 'Họ nên là một chuỗi.',
				'max:30' => 'Họ nên có độ dài tối đa 30.',
			],
			'data.attributes.phone' => [
				'string' => 'Số điện thoại nên là một chuỗi.',
				'regex' => 'Số điện thoại không hợp lệ.',
				'unique' => 'Số điện thoại đã được sử dụng.',
			],
			'data.attributes.email' => [
				'string' => 'Email nên là một chuỗi.',
				'lowercase' => 'Email nên viết thường.',
				'email' => 'Email không hợp lệ.',
				'max:50' => 'Email nên có độ dài tối đa 50.',
				'unique' => 'Email đã được sử dụng.',
			],
			'data.attributes.password' => [
				'confirmed' => 'Mật khẩu không khớp.',
				'password' => 'Mật khẩu nên chứa ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt.',
			],
		];
	}

	public function authorize(Request $request): bool
	{
		return $request->user()->hasPermissionTo('edit_users')
			|| $request->user()->id == $request->route('user');
	}
}
