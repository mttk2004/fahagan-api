<?php

namespace App\Http\Requests\V1;


use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;


class GenreStoreRequest extends FormRequest
{
	public function rules(): array
	{
		return [
			'data.attributes.name' => ['required', 'string'],
			'data.attributes.description' => ['required', 'string'],
		];
	}

	public function messages(): array
	{
		return [
			'data.attributes.name.required' => 'Tên thể loại là trường bắt buộc.',
			'data.attributes.name.string' => 'Tên thể loại nên là một chuỗi.',
			'data.attributes.description.required' => 'Mô tả thể loại là trường bắt buộc.',
			'data.attributes.description.string' => 'Mô tả thể loại nên là một chuỗi.',
		];
	}

	public function authorize(Request $request): bool
	{
		return $request->user()->hasPermissionTo('create_genres');
	}

	public function failedAuthorization()
	{
		throw new AuthorizationException('Bạn không có quyền thực hiện hành động này.');
	}
}
