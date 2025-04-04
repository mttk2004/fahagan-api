<?php

namespace App\Enums\Address;

use App\Traits\HasStandardValidationMessages;

enum AddressValidationMessages
{
    use HasStandardValidationMessages;

    case NAME_REQUIRED;
    case NAME_STRING;

    case PHONE_REQUIRED;
    case PHONE_STRING;
    case PHONE_REGEX;

    case CITY_REQUIRED;
    case CITY_STRING;

    case DISTRICT_REQUIRED;
    case DISTRICT_STRING;

    case WARD_REQUIRED;
    case WARD_STRING;

    case ADDRESS_LINE_REQUIRED;
    case ADDRESS_LINE_STRING;

    public function message(): string
    {
        return match($this) {
            self::NAME_REQUIRED => 'Tên là trường bắt buộc.',
            self::NAME_STRING => 'Tên nên là một chuỗi.',

            self::PHONE_REQUIRED => 'Số điện thoại là trường bắt buộc.',
            self::PHONE_STRING => 'Số điện thoại nên là một chuỗi.',
            self::PHONE_REGEX => 'Số điện thoại không hợp lệ.',

            self::CITY_REQUIRED => 'Thành phố là trường bắt buộc.',
            self::CITY_STRING => 'Thành phố nên là một chuỗi.',

            self::DISTRICT_REQUIRED => 'Quận/Huyện là trường bắt buộc.',
            self::DISTRICT_STRING => 'Quận/Huyện nên là một chuỗi.',

            self::WARD_REQUIRED => 'Phường/Xã là trường bắt buộc.',
            self::WARD_STRING => 'Phường/Xã nên là một chuỗi.',

            self::ADDRESS_LINE_REQUIRED => 'Địa chỉ là trường bắt buộc.',
            self::ADDRESS_LINE_STRING => 'Địa chỉ nên là một chuỗi.',
        };
    }

    public static function getMessages(): array
    {
        return [
            'name.required' => self::NAME_REQUIRED->message(),
            'name.string' => self::NAME_STRING->message(),

            'phone.required' => self::PHONE_REQUIRED->message(),
            'phone.string' => self::PHONE_STRING->message(),
            'phone.regex' => self::PHONE_REGEX->message(),

            'city.required' => self::CITY_REQUIRED->message(),
            'city.string' => self::CITY_STRING->message(),

            'district.required' => self::DISTRICT_REQUIRED->message(),
            'district.string' => self::DISTRICT_STRING->message(),

            'ward.required' => self::WARD_REQUIRED->message(),
            'ward.string' => self::WARD_STRING->message(),

            'address_line.required' => self::ADDRESS_LINE_REQUIRED->message(),
            'address_line.string' => self::ADDRESS_LINE_STRING->message(),
        ];
    }
}
