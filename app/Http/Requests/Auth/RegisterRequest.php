<?php

namespace App\Http\Requests\Auth;

use App\Enums\User\UserValidationMessages;
use App\Enums\User\UserValidationRules;
use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Models\User;
use App\Utils\AuthUtils;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        return [
            'first_name' => UserValidationRules::FIRST_NAME->rules(),
            'last_name' => UserValidationRules::LAST_NAME->rules(),
            'phone' => UserValidationRules::PHONE->rules(),
            'email' => UserValidationRules::EMAIL->rules(),
            'password' => UserValidationRules::PASSWORD->rules(),
            'is_customer' => UserValidationRules::IS_CUSTOMER->rules(),
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => UserValidationMessages::FIRST_NAME_REQUIRED->message(),
            'first_name.string' => UserValidationMessages::FIRST_NAME_STRING->message(),
            'first_name.max' => UserValidationMessages::FIRST_NAME_MAX->message(),

            'last_name.required' => UserValidationMessages::LAST_NAME_REQUIRED->message(),
            'last_name.string' => UserValidationMessages::LAST_NAME_STRING->message(),
            'last_name.max' => UserValidationMessages::LAST_NAME_MAX->message(),

            'email.required' => UserValidationMessages::EMAIL_REQUIRED->message(),
            'email.string' => UserValidationMessages::EMAIL_STRING->message(),
            'email.email' => UserValidationMessages::EMAIL_EMAIL->message(),
            'email.max' => UserValidationMessages::EMAIL_MAX->message(),
            'email.unique' => UserValidationMessages::EMAIL_UNIQUE->message(),

            'phone.required' => UserValidationMessages::PHONE_REQUIRED->message(),
            'phone.string' => UserValidationMessages::PHONE_STRING->message(),
            'phone.regex' => UserValidationMessages::PHONE_REGEX->message(),
            'phone.unique' => UserValidationMessages::PHONE_UNIQUE->message(),

            'password.required' => UserValidationMessages::PASSWORD_REQUIRED->message(),
            'password.string' => UserValidationMessages::PASSWORD_STRING->message(),
            'password.confirmed' => UserValidationMessages::PASSWORD_CONFIRMED->message(),
            'password.min' => UserValidationMessages::PASSWORD_MIN->message(),

            'is_customer.boolean' => UserValidationMessages::IS_CUSTOMER_BOOLEAN->message(),
        ];
    }

    public function authorize(): bool
    {
        return ! AuthUtils::user();
    }
}
