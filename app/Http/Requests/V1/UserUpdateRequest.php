<?php

namespace App\Http\Requests\V1;


use App\Http\Requests\BaseRequest;
use App\Models\User;
use App\Utils\AuthUtils;
use Illuminate\Http\Request;


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
		];
	}

	public function authorize(Request $request): bool
	{
		$user = AuthUtils::user();

		return AuthUtils::userCan('edit_users')
			|| $user->id == $request->route('user');
	}
}
