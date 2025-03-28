<?php

namespace App\Http\Requests\V1;


use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;

class AddressStoreRequest extends BaseRequest implements HasValidationMessages
{
	public function rules(): array
	{
		return [
			'name' => 'required|string',
			'phone' => [
				'required',
				'string',
				'regex:/^0[35789][0-9]{8}$/',
			],
			'city' => 'required|string',
			'ward' => 'required|string',
			'address_line' => 'required|string',
		];
	}

	public function messages(): array
	{
		return [
			'name.required' => 'Tên là trường bắt buộc.',
			'name.string' => 'Tên nên là một chuỗi.',
			'phone.required' => 'Số điện thoại là trường bắt buộc.',
			'phone.string' => 'Số điện thoại nên là một chuỗi.',
			'phone.regex' => 'Số điện thoại không hợp lệ.',
			'city.required' => 'Thành phố là trường bắt buộc.',
			'city.string' => 'Thành phố nên là một chuỗi.',
			'ward.required' => 'Quận/Huyện là trường bắt buộc.',
			'ward.string' => 'Quận/Huyện nên là một chuỗi.',
			'address_line.required' => 'Địa chỉ là trường bắt buộc.',
			'address_line.string' => 'Địa chỉ nên là một chuỗi.',
		];
	}

	public function authorize(): bool
	{
		return true;
	}
}
