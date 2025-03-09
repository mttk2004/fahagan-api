<?php

namespace App\Http\Requests\V1;


use Illuminate\Foundation\Http\FormRequest;


class BookStoreRequest extends FormRequest
{
	public function rules(): array
	{
		return [
			'title' => ['required'],
			'description' => ['required'],
			'price' => ['required', 'numeric'],
			'edition' => ['required', 'integer'],
			'pages' => ['required', 'integer'],
			'publication_date' => ['required', 'date'],
			'available_count' => ['required', 'integer'],
			'sold_count' => ['required', 'integer'],
			'publisher_id' => ['required', 'exists:publishers'],
		];
	}

	public function authorize(): bool
	{
		return true;
	}
}
