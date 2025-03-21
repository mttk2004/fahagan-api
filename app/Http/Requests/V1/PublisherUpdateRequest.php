<?php

namespace App\Http\Requests\V1;


use App\Http\Requests\BaseRequest;
use Illuminate\Http\Request;


class PublisherUpdateRequest extends BaseRequest
{
	public function rules(): array
	{
		return [
			'data.attributes.name' => ['sometimes', 'string'],
			'data.attributes.biography' => ['sometimes', 'string'],
		];
	}

	public function messages(): array
	{
		return [
			'data.attributes.name.string' => 'Tên nhà xuất bản nên là một chuỗi.',
			'data.attributes.biography.string' => 'Tiểu sử nhà xuất bản nên là một chuỗi.',
		];
	}

	public function authorize(Request $request): bool
	{
		return $request->user()->hasPermissionTo('edit_publishers');
	}
}
