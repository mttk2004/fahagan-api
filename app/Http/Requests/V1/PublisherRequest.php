<?php

namespace App\Http\Requests\V1;


use Illuminate\Foundation\Http\FormRequest;


class PublisherRequest extends FormRequest
{
	public function rules(): array
	{
		return [
			'name' => ['required'],
			'biography' => ['required'],
		];
	}

	public function authorize(): bool
	{
		return true;
	}
}
