<?php

namespace App\Http\Requests\V1;


use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Utils\AuthUtils;


class AuthorUpdateRequest extends BaseRequest implements HasValidationMessages
{
	public function rules(): array
	{
		return [
			'data.attributes.name' => [
				'sometimes',
				'string',
				'max:255',
			],
			'data.attributes.biography' => ['sometimes', 'string'],
		];
	}

	public function messages(): array
	{
		return [
			'data.attributes.name' => [
				'string' => 'Tên tác giả nên là một chuỗi.',
				'max:255' => 'Tên tác giả nên có độ dài tối đa 255.',
			],
			'data.attributes.biography' => [
				'string' => 'Tiểu sử tác giả nên là một chuỗi.',
			],
		];
	}

	public function authorize(): bool
	{
		return AuthUtils::userCan('edit_authors');
	}
}
