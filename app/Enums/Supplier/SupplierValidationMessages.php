<?php

namespace App\Enums\Supplier;

enum SupplierValidationMessages
{
    case NAME_REQUIRED;
    case NAME_STRING;
    case NAME_MAX;
    case NAME_UNIQUE;

    case PHONE_STRING;
    case PHONE_MAX;
    case PHONE_REGEX;

    case EMAIL_STRING;
    case EMAIL_EMAIL;
    case EMAIL_MAX;

    case CITY_STRING;
    case CITY_MAX;

    case DISTRICT_STRING;
    case DISTRICT_MAX;

    case WARD_STRING;
    case WARD_MAX;

    case ADDRESS_LINE_STRING;
    case ADDRESS_LINE_MAX;

    public function message(): string
    {
        return match ($this) {
            self::NAME_REQUIRED => 'Tên nhà cung cấp là trường bắt buộc.',
            self::NAME_STRING => 'Tên nhà cung cấp phải là kiểu chuỗi.',
            self::NAME_MAX => 'Tên nhà cung cấp không được vượt quá 255 ký tự.',
            self::NAME_UNIQUE => 'Tên nhà cung cấp đã tồn tại.',

            self::PHONE_STRING => 'Số điện thoại phải là kiểu chuỗi.',
            self::PHONE_MAX => 'Số điện thoại không được vượt quá 20 ký tự.',
            self::PHONE_REGEX => 'Số điện thoại không hợp lệ.',

            self::EMAIL_STRING => 'Email phải là kiểu chuỗi.',
            self::EMAIL_EMAIL => 'Email không đúng định dạng.',
            self::EMAIL_MAX => 'Email không được vượt quá 255 ký tự.',

            self::CITY_STRING => 'Thành phố phải là kiểu chuỗi.',
            self::CITY_MAX => 'Thành phố không được vượt quá 255 ký tự.',

            self::DISTRICT_STRING => 'Quận/Huyện phải là kiểu chuỗi.',
            self::DISTRICT_MAX => 'Quận/Huyện không được vượt quá 255 ký tự.',

            self::WARD_STRING => 'Phường/Xã phải là kiểu chuỗi.',
            self::WARD_MAX => 'Phường/Xã không được vượt quá 255 ký tự.',

            self::ADDRESS_LINE_STRING => 'Địa chỉ chi tiết phải là kiểu chuỗi.',
            self::ADDRESS_LINE_MAX => 'Địa chỉ chi tiết không được vượt quá 255 ký tự.',
        };
    }
}
