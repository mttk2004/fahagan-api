<?php

namespace App\Http\Requests\V1;


use App\Http\Requests\BaseRequest;
use Illuminate\Http\Request;


class PublisherStoreRequest extends BaseRequest
{
	public function rules(): array
	{
		return [
			'data.attributes.name' => ['required', 'string'],
			'data.attributes.biography' => ['required', 'string'],
		];
	}

	public function messages(): array
	{
		return [
			'data.attributes.name.required' => 'Tên nhà xuất bản là trường bắt buộc.',
			'data.attributes.name.string' => 'Tên nhà xuất bản nên là một chuỗi.',
			'data.attributes.biography.required' => 'Tiểu sử nhà xuất bản là trường bắt buộc.',
			'data.attributes.biography.string' => 'Tiểu sử nhà xuất bản nên là một chuỗi.',
		];
	}

	public function authorize(Request $request): bool
	{
		return $request->user()->hasPermissionTo('create_publishers');
	}
}
