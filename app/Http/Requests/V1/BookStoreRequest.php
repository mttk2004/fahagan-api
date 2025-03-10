<?php

namespace App\Http\Requests\V1;


use Illuminate\Foundation\Http\FormRequest;


class BookStoreRequest extends FormRequest
{
	public function rules(): array
	{
		return [
			'data.attributes.title' => [
				'required',
				'string',
				'max:255',
				'unique:books,title,NULL,id,edition,' . $this->input('data.attributes.edition'),
			],
			'data.attributes.description' => ['required', 'string'],
			'data.attributes.price' => ['required', 'numeric', 'min:200000', 'max:10000000'],
			'data.attributes.edition' => ['required', 'integer', 'min:1', 'max:30'],
			'data.attributes.pages' => ['required', 'integer', 'min:50', 'max:5000'],
			'data.attributes.publication_date' => ['required', 'date', 'before:today'],
			'data.relationships.authors.data.*.id' => ['required', 'integer', 'exists:authors,id'],
			'data.relationships.genres.data.*.id' => ['required', 'integer', 'exists:genres,id'],
			'data.relationships.publisher.id' => ['required', 'integer', 'exists:publishers,id'],
		];
	}

	public function messages(): array
	{
		return [
			'data.attributes.title' => [
				'required' => 'data.attributes.title là trường bắt buộc.',
				'string' => 'data.attributes.title nên là một chuỗi.',
				'max:255' => 'data.attributes.title nên có độ dài tối đa 255.',
				'unique' => 'data.attributes.title và data.attributes.edition nên là duy nhất, hãy thử thay đổi tile hoặc edition rồi thử lại.',
			],
			'data.attributes.description' => [
				'required' => 'data.attributes.description là trường bắt buộc.',
				'string' => 'data.attributes.description nên là một chuỗi.',
			],
			'data.attributes.price' => [
				'required' => 'data.attributes.price là trường bắt buộc.',
				'string' => 'data.attributes.price nên là một số thực.',
				'min:200000' => 'data.attributes.price nên có giá trị tối thiểu 200.000,0đ',
				'max:10000000' => 'data.attributes.price nên có giá trị tối đa 10.000.000,0đ',
			],
			'data.attributes.edition' => [
				'required' => 'data.attributes.edition là trường bắt buộc',
				'integer' => 'data.attributes.edition nên là một số nguyên',
				'min:1' => 'data.attributes.edition nên có giá thi tối thiểu 1',
				'max:30' => 'data.attributes.edition nên có giá trị tối đa 30',
			],
			'data.attributes.pages' => [
				'required' => 'data.attributes.pages là trường bắt buộc',
				'integer' => 'data.attributes.pages nên là một số nguyên',
				'min:50' => 'data.attributes.pages nên có giá thi tối thiểu 50',
				'max:5000' => 'data.attributes.pages nên có giá trị tối đa 5000',
			],
			'data.attributes.publication_date' => [
				'required' => 'data.attributes.publication_date là trường bắt buộc',
				'date' => 'data.attributes.publication_date nên là một ngày',
				'before:today' => 'data.attributes.publication_date nên trước ngày hôm nay',
			],
			'data.relationships.authors.data.*.id' => [
				'required' => 'data.relationships.authors.data.*.id là trường bắt buộc',
				'integer' => 'data.relationships.authors.data.*.id nên là một số nguyên',
				'exists' => 'data.relationships.authors.data.*.id không tồn tại',
			],
			'data.relationships.genres.data.*.id' => [
				'required' => 'data.relationships.genres.data.*.id là trường bắt buộc',
				'integer' => 'data.relationships.genres.data.*.id nên là một số nguyên',
				'exists' => 'data.relationships.genres.data.*.id không tồn tại',
			],
			'data.relationships.publisher.id' => [
				'required' => 'data.relationships.publisher.id là trường bắt buộc',
				'integer' => 'data.relationships.publisher.id nên là một số nguyên',
				'exists' => 'data.relationships.publisher.id không tồn tại',
			],
		];
	}

	public function authorize(): bool
	{
		return true;
	}
}
