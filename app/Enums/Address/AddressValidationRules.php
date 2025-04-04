<?php

namespace App\Enums\Address;

use App\Traits\HasStandardValidationRules;
use App\Traits\HasUpdateRules;

enum AddressValidationRules
{
    use HasUpdateRules;
    use HasStandardValidationRules;

    case NAME;
    case PHONE;
    case CITY;
    case DISTRICT;
    case WARD;
    case ADDRESS_LINE;

    public function rules(): array
    {
        return match($this) {
            self::NAME => ['required', 'string'],
            self::PHONE => ['required', 'string', 'regex:/^0[35789][0-9]{8}$/'],
            self::CITY => ['required', 'string'],
            self::DISTRICT => ['required', 'string'],
            self::WARD => ['required', 'string'],
            self::ADDRESS_LINE => ['required', 'string'],
        };
    }

    /**
     * Lấy quy tắc validation cho creation
     */
    public static function getCreationRules(): array
    {
        return [
            'name' => self::NAME->rules(),
            'phone' => self::PHONE->rules(),
            'city' => self::CITY->rules(),
            'district' => self::DISTRICT->rules(),
            'ward' => self::WARD->rules(),
            'address_line' => self::ADDRESS_LINE->rules(),
        ];
    }

    /**
     * Lấy quy tắc validation cho update
     */
    public static function getUpdateRules(): array
    {
        return [
            'name' => self::transformToUpdateRules(self::NAME->rules()),
            'phone' => self::transformToUpdateRules(self::PHONE->rules()),
            'city' => self::transformToUpdateRules(self::CITY->rules()),
            'district' => self::transformToUpdateRules(self::DISTRICT->rules()),
            'ward' => self::transformToUpdateRules(self::WARD->rules()),
            'address_line' => self::transformToUpdateRules(self::ADDRESS_LINE->rules()),
        ];
    }
}
