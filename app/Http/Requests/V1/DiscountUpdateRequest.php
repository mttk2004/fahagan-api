<?php

namespace App\Http\Requests\V1;


use Illuminate\Foundation\Http\FormRequest;


class DiscountUpdateRequest extends FormRequest
{
	public function rules(): array
	{
		return [
			
		];
	}

	public function authorize(): bool
	{
		return true;
	}
}
