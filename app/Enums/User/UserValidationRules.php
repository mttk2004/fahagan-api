<?php

namespace App\Enums\User;

use App\Models\User;
use Illuminate\Validation\Rules\Password;

enum UserValidationRules
{
    case FIRST_NAME;
    case LAST_NAME;
    case EMAIL;
    case PHONE;
    case PASSWORD;
    case IS_CUSTOMER;

    public function rules(): array
    {
        return match($this) {
            self::FIRST_NAME => ['required', 'string', 'max:30'],
            self::LAST_NAME => ['required', 'string', 'max:30'],
            self::EMAIL => ['required', 'string', 'email', 'max:50', 'unique:' . User::class],
            self::PHONE => ['required', 'string', 'regex:/^0[35789][0-9]{8}$/', 'unique:' . User::class],
            self::PASSWORD => ['required', 'string', 'confirmed', Password::default()],
            self::IS_CUSTOMER => ['sometimes', 'boolean'],
        };
    }
}
