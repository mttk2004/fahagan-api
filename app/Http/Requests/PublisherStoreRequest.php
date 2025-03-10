<?php

namespace App\Http\Requests;


use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;


class PublisherStoreRequest extends FormRequest
{
	public function rules(): array
	{
		return [
			'name' => ['required', 'string'],
			'biography' => ['required', 'string'],
		];
	}

	public function messages()
	{
		return [
			'name.required' => 'Tên nhà xuất bản là trường bắt buộc.',
			'name.string' => 'Tên nhà xuất bản nên là một chuỗi.',
			'biography.required' => 'Tiểu sử nhà xuất bản là trường bắt buộc.',
			'biography.string' => 'Tiểu sử nhà xuất bản nên là một chuỗi.',
		];
	}

	public function authorize(Request $request): bool
	{
		return $request->user()->hasPermissionTo('create publishers');
	}

	public function failedAuthorization()
	{
		throw new AuthorizationException('Bạn không có quyền thực hiện thao tác này.');
	}
}
