<?php

namespace App\Enums\Publisher;

enum PublisherValidationMessages
{
    case NAME_REQUIRED;
    case NAME_STRING;
    case NAME_MAX;
    case NAME_UNIQUE;

    case BIOGRAPHY_REQUIRED;
    case BIOGRAPHY_STRING;

    public function message(): string
    {
        return match($this) {
            self::NAME_REQUIRED => 'Tên nhà xuất bản là trường bắt buộc.',
            self::NAME_STRING => 'Tên nhà xuất bản nên là một chuỗi.',
            self::NAME_MAX => 'Tên nhà xuất bản nên có độ dài tối đa 255.',
            self::NAME_UNIQUE => 'Tên nhà xuất bản đã tồn tại.',

            self::BIOGRAPHY_REQUIRED => 'Tiểu sử nhà xuất bản là trường bắt buộc.',
            self::BIOGRAPHY_STRING => 'Tiểu sử nhà xuất bản nên là một chuỗi.',
        };
    }
}
