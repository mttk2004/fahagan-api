<?php

namespace App\Enums\Author;

use App\Traits\HasStandardValidationMessages;

enum AuthorValidationMessages
{
    use HasStandardValidationMessages;

    case NAME_REQUIRED;
    case NAME_STRING;
    case NAME_MAX;

    case BIOGRAPHY_REQUIRED;
    case BIOGRAPHY_STRING;

    case IMAGE_URL_REQUIRED;
    case IMAGE_URL_STRING;
    case IMAGE_URL_URL;

    case BOOK_ID_REQUIRED;
    case BOOK_ID_INTEGER;
    case BOOK_ID_EXISTS;

    public function message(): string
    {
        return match($this) {
            self::NAME_REQUIRED => 'Tên tác giả là trường bắt buộc.',
            self::NAME_STRING => 'Tên tác giả nên là một chuỗi.',
            self::NAME_MAX => 'Tên tác giả nên có độ dài tối đa 255.',

            self::BIOGRAPHY_REQUIRED => 'Tiểu sử tác giả là trường bắt buộc.',
            self::BIOGRAPHY_STRING => 'Tiểu sử tác giả nên là một chuỗi.',

            self::IMAGE_URL_REQUIRED => 'URL hình ảnh tác giả là trường bắt buộc.',
            self::IMAGE_URL_STRING => 'URL hình ảnh tác giả nên là một chuỗi.',
            self::IMAGE_URL_URL => 'URL hình ảnh tác giả không hợp lệ.',

            self::BOOK_ID_REQUIRED => 'id của Sách là trường bắt buộc.',
            self::BOOK_ID_INTEGER => 'id của Sách nên là một số nguyên.',
            self::BOOK_ID_EXISTS => 'id của Sách không tồn tại.',
        };
    }
}
