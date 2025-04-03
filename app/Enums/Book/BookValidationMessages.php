<?php

namespace App\Enums\Book;

enum BookValidationMessages
{
    case TITLE_REQUIRED;
    case TITLE_STRING;
    case TITLE_MAX;
    case TITLE_UNIQUE;

    case DESCRIPTION_REQUIRED;
    case DESCRIPTION_STRING;

    case PRICE_REQUIRED;
    case PRICE_NUMERIC;
    case PRICE_MIN;
    case PRICE_MAX;

    case EDITION_REQUIRED;
    case EDITION_INTEGER;
    case EDITION_MIN;
    case EDITION_MAX;

    case PAGES_REQUIRED;
    case PAGES_INTEGER;
    case PAGES_MIN;
    case PAGES_MAX;

    case IMAGE_URL_REQUIRED;
    case IMAGE_URL_STRING;
    case IMAGE_URL_URL;

    case PUBLICATION_DATE_REQUIRED;
    case PUBLICATION_DATE_DATE;
    case PUBLICATION_DATE_BEFORE;

    case AUTHOR_ID_REQUIRED;
    case AUTHOR_ID_INTEGER;
    case AUTHOR_ID_EXISTS;

    case GENRE_ID_REQUIRED;
    case GENRE_ID_INTEGER;
    case GENRE_ID_EXISTS;

    case PUBLISHER_ID_REQUIRED;
    case PUBLISHER_ID_INTEGER;
    case PUBLISHER_ID_EXISTS;

    public function message(): string
    {
        return match($this) {
            self::TITLE_REQUIRED => 'Tiêu đề sách là trường bắt buộc.',
            self::TITLE_STRING => 'Tiêu đề sách nên là một chuỗi.',
            self::TITLE_MAX => 'Tiêu đề sách nên có độ dài tối đa 255.',
            self::TITLE_UNIQUE => 'Tiêu đề sách và Số phiên bản nên là duy nhất, hãy thử thay đổi title hoặc edition rồi thực hiện lại.',

            self::DESCRIPTION_REQUIRED => 'Mô tả sách là trường bắt buộc.',
            self::DESCRIPTION_STRING => 'Mô tả sách nên là một chuỗi.',

            self::PRICE_REQUIRED => 'Giá sách là trường bắt buộc.',
            self::PRICE_NUMERIC => 'Giá sách nên là một số thực.',
            self::PRICE_MIN => 'Giá sách nên có giá trị tối thiểu 200.000,0đ',
            self::PRICE_MAX => 'Giá sách nên có giá trị tối đa 10.000.000,0đ',

            self::EDITION_REQUIRED => 'Số phiên bản là trường bắt buộc',
            self::EDITION_INTEGER => 'Số phiên bản nên là một số nguyên',
            self::EDITION_MIN => 'Số phiên bản nên có giá thi tối thiểu 1',
            self::EDITION_MAX => 'Số phiên bản nên có giá trị tối đa 30',

            self::PAGES_REQUIRED => 'Số trang là trường bắt buộc',
            self::PAGES_INTEGER => 'Số trang nên là một số nguyên',
            self::PAGES_MIN => 'Số trang nên có giá thi tối thiểu 50',
            self::PAGES_MAX => 'Số trang nên có giá trị tối đa 5000',

            self::IMAGE_URL_REQUIRED => 'URL hình ảnh là trường bắt buộc',
            self::IMAGE_URL_STRING => 'URL hình ảnh nên là một chuỗi',
            self::IMAGE_URL_URL => 'URL hình ảnh không hợp lệ',

            self::PUBLICATION_DATE_REQUIRED => 'Ngày xuất bản là trường bắt buộc',
            self::PUBLICATION_DATE_DATE => 'Ngày xuất bản nên là một ngày',
            self::PUBLICATION_DATE_BEFORE => 'Ngày xuất bản nên trước ngày hôm nay',

            self::AUTHOR_ID_REQUIRED => 'id của Tác giả là trường bắt buộc',
            self::AUTHOR_ID_INTEGER => 'id của Tác giả nên là một số nguyên',
            self::AUTHOR_ID_EXISTS => 'id của Tác giả không tồn tại',

            self::GENRE_ID_REQUIRED => 'id của Thể loại là trường bắt buộc',
            self::GENRE_ID_INTEGER => 'id của Thể loại nên là một số nguyên',
            self::GENRE_ID_EXISTS => 'id của Thể loại không tồn tại',

            self::PUBLISHER_ID_REQUIRED => 'id của Nhà xuất bản là trường bắt buộc',
            self::PUBLISHER_ID_INTEGER => 'id của Nhà xuất bản nên là một số nguyên',
            self::PUBLISHER_ID_EXISTS => 'id của Nhà xuất bản không tồn tại',
        };
    }
}
