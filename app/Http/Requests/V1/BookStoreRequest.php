<?php

namespace App\Http\Requests\V1;


use Illuminate\Foundation\Http\FormRequest;


class BookStoreRequest extends FormRequest
{
	public function rules(): array
	{
		return [
			'data.attributes.title' => ['required', 'string', 'max:255', 'unique:books,title,NULL,id,edition,' . $this->input('data.attributes.edition')],
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

	public function authorize(): bool
	{
		return true;
	}
}
