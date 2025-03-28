<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Models\User;
use App\Utils\AuthUtils;
use Illuminate\Validation\Rules\Password;


class RegisterRequest extends BaseRequest implements HasValidationMessages
{
	public function rules(): array
	{
		return [
			'first_name' => ['required', 'string', 'max:30'],
			'last_name' => ['required', 'string', 'max:30'],
			'phone' => [
				'required',
				'string',
				'regex:/^0[35789][0-9]{8}$/',
				'unique:' . User::class,
			],
			'email' => [
				'required',
				'string',
				'email',
				'max:50',
				'unique:' . User::class,
			],
			'password' => [
				'required',
				'string',
				'confirmed',
				Password::default
				(),
			],
		];
	}

	public function messages(): array
	{
		return [
			'first_name' => [
				'required' => 'Tên là trường bắt buộc.',
				'string' => 'Tên nên là một chuỗi.',
				'max:30' => 'Tên nên có độ dài tối đa 30.',
			],
			'last_name' => [
				'required' => 'Họ là trường bắt buộc.',
				'string' => 'Họ nên là một chuỗi.',
				'max:30' => 'Họ nên có độ dài tối đa 30.',
			],
			'phone' => [
				'required' => 'Số điện thoại là trường bắt buộc.',
				'string' => 'Số điện thoại nên là một chuỗi.',
				'regex' => 'Số điện thoại không hợp lệ.',
				'unique' => 'Số điện thoại đã được sử dụng.',
			],
			'email' => [
				'required' => 'Email là trường bắt buộc.',
				'string' => 'Email nên là một chuỗi.',
				'email' => 'Email không hợp lệ.',
				'max:50' => 'Email nên có độ dài tối đa 50.',
				'unique' => 'Email đã được sử dụng.',
			],
			'password' => [
				'required' => 'Mật khẩu là trường bắt buộc.',
				'string' => 'Mật khẩu nên là một chuỗi.',
				'confirmed' => 'Mật khẩu không khớp.',
				'password' => 'Mật khẩu nên chứa ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt.',
			],
		];
	}

	public function authorize(): bool
	{
		return !AuthUtils::user();
	}
}
