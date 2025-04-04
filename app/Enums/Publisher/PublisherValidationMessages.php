<?php

namespace App\Enums\Publisher;

use App\Traits\HasStandardValidationMessages;

enum PublisherValidationMessages
{
    use HasStandardValidationMessages;

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
            self::NAME_STRING => 'Tên nhà xuất bản phải là kiểu chuỗi.',
            self::NAME_MAX => 'Tên nhà xuất bản không được vượt quá 255 ký tự.',
            self::NAME_UNIQUE => 'Tên nhà xuất bản đã tồn tại trong hệ thống.',

            self::BIOGRAPHY_REQUIRED => 'Tiểu sử nhà xuất bản là trường bắt buộc.',
            self::BIOGRAPHY_STRING => 'Tiểu sử nhà xuất bản phải là kiểu chuỗi.',
        };
    }
}
