<?php

namespace App\Enums\Genre;

use App\Traits\HasStandardValidationMessages;

enum GenreValidationMessages
{
    use HasStandardValidationMessages;

    case NAME_REQUIRED;
    case NAME_STRING;
    case NAME_MAX;
    case NAME_UNIQUE;

    case SLUG_REQUIRED;
    case SLUG_STRING;
    case SLUG_MAX;
    case SLUG_UNIQUE;

    case DESCRIPTION_SOMETIMES;
    case DESCRIPTION_NULLABLE;
    case DESCRIPTION_STRING;
    case DESCRIPTION_MAX;

    public function message(): string
    {
        return match($this) {
            self::NAME_REQUIRED => 'Tên thể loại là trường bắt buộc.',
            self::NAME_STRING => 'Tên thể loại nên là một chuỗi.',
            self::NAME_MAX => 'Tên thể loại nên có độ dài tối đa 50 ký tự.',
            self::NAME_UNIQUE => 'Tên thể loại đã tồn tại.',

            self::SLUG_REQUIRED => 'Slug là trường bắt buộc.',
            self::SLUG_STRING => 'Slug nên là một chuỗi.',
            self::SLUG_MAX => 'Slug nên có độ dài tối đa 100 ký tự.',
            self::SLUG_UNIQUE => 'Slug đã tồn tại.',

            self::DESCRIPTION_SOMETIMES => 'Mô tả có thể có hoặc không.',
            self::DESCRIPTION_NULLABLE => 'Mô tả có thể có giá trị null.',
            self::DESCRIPTION_STRING => 'Mô tả nên là một chuỗi.',
            self::DESCRIPTION_MAX => 'Mô tả nên có độ dài tối đa 500 ký tự.',
        };
    }
}
