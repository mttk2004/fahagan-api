<?php

namespace App\Http\Requests\V1;


use Illuminate\Foundation\Http\FormRequest;


class UserUpdateRequest extends FormRequest
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
