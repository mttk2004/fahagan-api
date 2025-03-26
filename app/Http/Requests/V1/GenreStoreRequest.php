<?php

namespace App\Http\Requests\V1;


use App\Http\Requests\BaseRequest;
use Auth;


class GenreStoreRequest extends BaseRequest
{
	public function rules(): array
	{
		return [
			'data.attributes.name' => ['required', 'string', 'max:255', 'unique:genres,name'],
			'data.attributes.description' => ['required', 'string'],
		];
	}

	public function messages(): array
	{
		return [
			'data.attributes.name.required' => 'Tên thể loại là trường bắt buộc.',
			'data.attributes.name.string' => 'Tên thể loại nên là một chuỗi.',
			'data.attributes.name.max' => 'Tên thể loại nên có độ dài tối đa 255.',
			'data.attributes.name.unique' => 'Tên thể loại đã tồn tại.',
			'data.attributes.description.required' => 'Mô tả thể loại là trường bắt buộc.',
			'data.attributes.description.string' => 'Mô tả thể loại nên là một chuỗi.',
		];
	}

	public function authorize(): bool
	{
		return Auth::guard('sanctum')->user()->hasPermissionTo('create_genres');
	}
}
