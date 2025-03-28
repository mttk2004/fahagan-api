<?php

namespace App\Http\Requests\V1;


use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Utils\AuthUtils;


class PublisherUpdateRequest extends BaseRequest implements HasValidationMessages
{
	public function rules(): array
	{
		return [
			'data.attributes.name' => ['sometimes', 'string', 'max:255', 'unique:publishers,name'],
			'data.attributes.biography' => ['sometimes', 'string'],
		];
	}

	public function messages(): array
	{
		return [
			'data.attributes.name.string' => 'Tên nhà xuất bản nên là một chuỗi.',
			'data.attributes.name.max' => 'Tên nhà xuất bản nên có độ dài tối đa 255.',
			'data.attributes.name.unique' => 'Tên nhà xuất bản đã tồn tại.',
			'data.attributes.biography.string' => 'Tiểu sử nhà xuất bản nên là một chuỗi.',
		];
	}

	public function authorize(): bool
	{
		return AuthUtils::userCan('edit_publishers');
	}
}
