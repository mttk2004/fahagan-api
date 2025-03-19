<?php

namespace App\Http\Requests\V1;


use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;


class AuthorStoreRequest extends FormRequest
{
	public function rules(): array
	{
		return [
			'data.attributes.name' => [
				'required',
				'string',
				'max:255',
			],
			'data.attributes.biography' => ['required', 'string'],
		];
	}

	public function messages(): array
	{
		return [
			'data.attributes.name' => [
				'required' => 'Tên tác giả là trường bắt buộc.',
				'string' => 'Tên tác giả nên là một chuỗi.',
				'max:255' => 'Tên tác giả nên có độ dài tối đa 255.',
			],
			'data.attributes.biography' => [
				'required' => 'Tiểu sử tác giả là trường bắt buộc.',
				'string' => 'Tiểu sử tác giả nên là một chuỗi.',
			],
		];
	}

	public function authorize(Request $request): bool
	{
		return $request->user()->checkPermissionTo('create_authors');
	}

	public function failedAuthorization()
	{
		throw new AuthorizationException('Bạn không có quyền thực hiện hành động này.');
	}
}
