<?php

namespace App\Http\Requests\V1;


use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;


class PublisherUpdateRequest extends FormRequest
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

	public function failedAuthorization()
	{
		throw new AuthorizationException('Bạn không có quyền thực hiện hành động này.');
	}
}
