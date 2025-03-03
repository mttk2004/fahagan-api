<?php

namespace App\Http\Requests\Auth;


use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;


class RegisterRequest extends FormRequest
{
	public function rules(): array
	{
		return [
			'first_name' => ['required', 'string', 'max:30'],
			'last_name' => ['required', 'string', 'max:30'],
			'phone' => [
				'required',
				'string',
				'regex:/^0[35789][0-9]{8}$/',
				'unique:' . User::class,
			],
			'email' => [
				'required',
				'string',
				'lowercase',
				'email',
				'max:50',
				'unique:' . User::class,
			],
			'password' => ['required', 'confirmed', Password::default()],
		];
	}

	public function authorize(): bool
	{
		return true;
	}
}
