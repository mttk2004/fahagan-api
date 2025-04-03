<?php

namespace App\Enums\User;

enum UserValidationMessages
{
    case FIRST_NAME_REQUIRED;
    case FIRST_NAME_STRING;
    case FIRST_NAME_MAX;

    case LAST_NAME_REQUIRED;
    case LAST_NAME_STRING;
    case LAST_NAME_MAX;

    case EMAIL_REQUIRED;
    case EMAIL_STRING;
    case EMAIL_EMAIL;
    case EMAIL_MAX;
    case EMAIL_UNIQUE;

    case PHONE_REQUIRED;
    case PHONE_STRING;
    case PHONE_REGEX;
    case PHONE_UNIQUE;

    case PASSWORD_REQUIRED;
    case PASSWORD_STRING;
    case PASSWORD_CONFIRMED;
    case PASSWORD_MIN;

    case IS_CUSTOMER_BOOLEAN;

    public function message(): string
    {
        return match($this) {
            self::FIRST_NAME_REQUIRED => 'Tên là trường bắt buộc.',
            self::FIRST_NAME_STRING => 'Tên nên là một chuỗi.',
            self::FIRST_NAME_MAX => 'Tên nên có độ dài tối đa 30.',

            self::LAST_NAME_REQUIRED => 'Họ là trường bắt buộc.',
            self::LAST_NAME_STRING => 'Họ nên là một chuỗi.',
            self::LAST_NAME_MAX => 'Họ nên có độ dài tối đa 30.',

            self::EMAIL_REQUIRED => 'Email là trường bắt buộc.',
            self::EMAIL_STRING => 'Email nên là một chuỗi.',
            self::EMAIL_EMAIL => 'Email không hợp lệ.',
            self::EMAIL_MAX => 'Email nên có độ dài tối đa 50.',
            self::EMAIL_UNIQUE => 'Email đã được sử dụng.',

            self::PHONE_REQUIRED => 'Số điện thoại là trường bắt buộc.',
            self::PHONE_STRING => 'Số điện thoại nên là một chuỗi.',
            self::PHONE_REGEX => 'Số điện thoại không hợp lệ.',
            self::PHONE_UNIQUE => 'Số điện thoại đã được sử dụng.',

            self::PASSWORD_REQUIRED => 'Mật khẩu là trường bắt buộc.',
            self::PASSWORD_STRING => 'Mật khẩu nên là một chuỗi.',
            self::PASSWORD_CONFIRMED => 'Mật khẩu không khớp.',
            self::PASSWORD_MIN => 'Mật khẩu nên có độ dài tối thiểu 8 ký tự.',

            self::IS_CUSTOMER_BOOLEAN => 'Trường này phải là giá trị boolean.',
        };
    }
}
