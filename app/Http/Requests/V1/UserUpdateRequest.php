<?php

namespace App\Http\Requests\V1;

use App\Enums\User\UserValidationMessages;
use App\Enums\User\UserValidationRules;
use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Models\User;
use App\Utils\AuthUtils;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        // Lấy ID người dùng từ route parameter
        $userId = request()->route('user');

        return [
            'data.attributes.first_name' => array_merge(
                ['sometimes'],
                array_filter(UserValidationRules::FIRST_NAME->rules(), fn ($rule) => $rule !== 'required')
            ),
            'data.attributes.last_name' => array_merge(
                ['sometimes'],
                array_filter(UserValidationRules::LAST_NAME->rules(), fn ($rule) => $rule !== 'required')
            ),
            'data.attributes.phone' => array_merge(
                ['sometimes'],
                array_filter(
                    UserValidationRules::PHONE->rules(),
                    fn ($rule) => $rule !== 'required' && $rule !== 'unique:' . User::class
                ),
                [Rule::unique('users', 'phone')->ignore($userId)]
            ),
            'data.attributes.email' => array_merge(
                ['sometimes'],
                array_filter(
                    UserValidationRules::EMAIL->rules(),
                    fn ($rule) => $rule !== 'required' && $rule !== 'unique:' . User::class
                ),
                [Rule::unique('users', 'email')->ignore($userId)]
            ),
        ];
    }

    public function messages(): array
    {
        return [
            'data.attributes.first_name.string' => UserValidationMessages::FIRST_NAME_STRING->message(),
            'data.attributes.first_name.max' => UserValidationMessages::FIRST_NAME_MAX->message(),

            'data.attributes.last_name.string' => UserValidationMessages::LAST_NAME_STRING->message(),
            'data.attributes.last_name.max' => UserValidationMessages::LAST_NAME_MAX->message(),

            'data.attributes.email.string' => UserValidationMessages::EMAIL_STRING->message(),
            'data.attributes.email.email' => UserValidationMessages::EMAIL_EMAIL->message(),
            'data.attributes.email.max' => UserValidationMessages::EMAIL_MAX->message(),
            'data.attributes.email.unique' => UserValidationMessages::EMAIL_UNIQUE->message(),

            'data.attributes.phone.string' => UserValidationMessages::PHONE_STRING->message(),
            'data.attributes.phone.regex' => UserValidationMessages::PHONE_REGEX->message(),
            'data.attributes.phone.unique' => UserValidationMessages::PHONE_UNIQUE->message(),
        ];
    }

    public function authorize(): bool
    {
        return AuthUtils::user() && AuthUtils::user()->id == request()->route('user');
    }
}
