<?php

namespace App\Enums\User;

use App\Abstracts\BaseValidationRules;
use App\Traits\HasUniqueRules;
use Illuminate\Validation\Rules\Password;

enum UserValidationRules
{
    use BaseValidationRules;

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
            self::EMAIL => ['required', 'string', 'email', 'max:50'],
            self::PHONE => ['required', 'string', 'regex:/^0[35789][0-9]{8}$/'],
            self::PASSWORD => ['required', 'string', 'confirmed', Password::default()],
            self::IS_CUSTOMER => ['sometimes', 'boolean'],
        };
    }

    /**
     * Lấy quy tắc validation cho email với kiểm tra unique
     */
    public static function getEmailRuleWithUnique(?string $userId = null): array
    {
        return array_merge(
            self::EMAIL->rules(),
            [HasUniqueRules::createUniqueRule('users', 'email', $userId)]
        );
    }

    /**
     * Lấy quy tắc validation cho phone với kiểm tra unique
     */
    public static function getPhoneRuleWithUnique(?string $userId = null): array
    {
        return array_merge(
            self::PHONE->rules(),
            [HasUniqueRules::createUniqueRule('users', 'phone', $userId)]
        );
    }
}
