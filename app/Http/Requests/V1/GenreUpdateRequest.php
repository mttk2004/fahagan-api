<?php

namespace App\Http\Requests\V1;


use App\Http\Requests\BaseRequest;
use Illuminate\Http\Request;


class GenreUpdateRequest extends BaseRequest
{
	public function rules(): array
	{
		return [
			'data.attributes.name' => ['sometimes', 'string', 'max:255', 'unique:genres,name'],
			'data.attributes.description' => ['sometimes', 'string'],
		];
	}

	public function messages(): array
	{
		return [
			'data.attributes.name.string' => 'Tên thể loại nên là một chuỗi.',
			'data.attributes.name.max' => 'Tên thể loại nên có độ dài tối đa 255.',
			'data.attributes.name.unique' => 'Tên thể loại đã tồn tại.',
			'data.attributes.description.string' => 'Mô tả thể loại nên là một chuỗi.',
		];
	}

	public function authorize(Request $request): bool
	{
		return $request->user()->hasPermissionTo('edit_genres');
	}
}
