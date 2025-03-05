<?php

namespace App\Http\Requests\V1;


use Illuminate\Foundation\Http\FormRequest;


class BookRequest extends FormRequest
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
			'sold_count' => ['required', 'integer'],
		];
	}

	public function authorize(): bool
	{
		return true;
	}
}
