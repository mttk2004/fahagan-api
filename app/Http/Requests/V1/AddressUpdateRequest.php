<?php

namespace App\Http\Requests\V1;


use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;

class AddressUpdateRequest extends BaseRequest implements HasValidationMessages
{
	public function rules(): array
	{
		return [
			'name' => 'sometimes|string',
			'phone' => [
				'sometimes',
				'string',
				'regex:/^0[35789][0-9]{8}$/',
			],
			'city' => 'sometimes|string',
			'ward' => 'sometimes|string',
			'address_line' => 'sometimes|string',
		];
	}

	public function messages(): array
	{
		return [
			'name.string' => 'Tên nên là một chuỗi.',
			'phone.string' => 'Số điện thoại nên là một chuỗi.',
			'phone.regex' => 'Số điện thoại không hợp lệ.',
			'city.string' => 'Thành phố nên là một chuỗi.',
			'ward.string' => 'Quận/Huyện nên là một chuỗi.',
			'address_line.string' => 'Địa chỉ nên là một chuỗi.',
		];
	}

	public function authorize(): bool
	{
		return true;
	}
}
