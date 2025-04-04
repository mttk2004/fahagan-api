<?php

namespace App\Http\Requests\Auth;

use App\Enums\User\UserValidationMessages;
use App\Enums\User\UserValidationRules;
use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Traits\HasApiJsonValidation;
use App\Utils\AuthUtils;

class RegisterRequest extends BaseRequest implements HasValidationMessages
{
    use HasApiJsonValidation;

    public function rules(): array
    {
        $attributesRules = $this->mapAttributesRules([
            'first_name' => UserValidationRules::FIRST_NAME->rules(),
            'last_name' => UserValidationRules::LAST_NAME->rules(),
            'phone' => UserValidationRules::getPhoneRuleWithUnique(),
            'email' => UserValidationRules::getEmailRuleWithUnique(),
            'password' => UserValidationRules::PASSWORD->rules(),
            'password_confirmation' => ['required', 'same:data.attributes.password'],
            'is_customer' => UserValidationRules::IS_CUSTOMER->rules(),
        ]);

        return $attributesRules;
    }

    public function messages(): array
    {
        return array_merge(
            UserValidationMessages::getJsonApiMessages(),
            [
                'data.attributes.password_confirmation.required' => 'Xác nhận mật khẩu là bắt buộc.',
                'data.attributes.password_confirmation.same' => 'Xác nhận mật khẩu không khớp với mật khẩu.',
            ]
        );
    }

    public function authorize(): bool
    {
        return ! AuthUtils::user();
    }
}
