<?php

namespace App\Enums\Book;

enum BookValidationMessages: string
{
    case TITLE_REQUIRED = 'Tiêu đề sách là trường bắt buộc.';
    case TITLE_STRING = 'Tiêu đề sách nên là một chuỗi.';
    case TITLE_MAX = 'Tiêu đề sách nên có độ dài tối đa 255.';
    case TITLE_UNIQUE = 'Tiêu đề sách và Số phiên bản nên là duy nhất, hãy thử thay đổi tile hoặc edition rồi thực hiện lại.';

    case DESCRIPTION_REQUIRED = 'Mô tả sách là trường bắt buộc.';
    case DESCRIPTION_STRING = 'Mô tả sách nên là một chuỗi.';

    case PRICE_REQUIRED = 'Giá sách là trường bắt buộc.';
    case PRICE_NUMERIC = 'Giá sách nên là một số thực.';
    case PRICE_MIN = 'Giá sách nên có giá trị tối thiểu 200.000,0đ';
    case PRICE_MAX = 'Giá sách nên có giá trị tối đa 10.000.000,0đ';

    case EDITION_REQUIRED = 'Số phiên bản là trường bắt buộc';
    case EDITION_INTEGER = 'Số phiên bản nên là một số nguyên';
    case EDITION_MIN = 'Số phiên bản nên có giá thi tối thiểu 1';
    case EDITION_MAX = 'Số phiên bản nên có giá trị tối đa 30';

    case PAGES_REQUIRED = 'Số trang là trường bắt buộc';
    case PAGES_INTEGER = 'Số trang nên là một số nguyên';
    case PAGES_MIN = 'Số trang nên có giá thi tối thiểu 50';
    case PAGES_MAX = 'Số trang nên có giá trị tối đa 5000';

    case IMAGE_URL_STRING = 'URL hình ảnh nên là một chuỗi';

    case PUBLICATION_DATE_REQUIRED = 'Ngày xuất bản là trường bắt buộc';
    case PUBLICATION_DATE_DATE = 'Ngày xuất bản nên là một ngày';
    case PUBLICATION_DATE_BEFORE = 'Ngày xuất bản nên trước ngày hôm nay';

    case AUTHOR_ID_REQUIRED = 'id của Tác giả là trường bắt buộc';
    case AUTHOR_ID_INTEGER = 'id của Tác giả nên là một số nguyên';
    case AUTHOR_ID_EXISTS = 'id của Tác giả không tồn tại';

    case GENRE_ID_REQUIRED = 'id của Thể loại là trường bắt buộc';
    case GENRE_ID_INTEGER = 'id của Thể loại nên là một số nguyên';
    case GENRE_ID_EXISTS = 'id của Thể loại không tồn tại';

    case PUBLISHER_ID_REQUIRED = 'id của Nhà xuất bản là trường bắt buộc';
    case PUBLISHER_ID_INTEGER = 'id của Nhà xuất bản nên là một số nguyên';
    case PUBLISHER_ID_EXISTS = 'id của Nhà xuất bản không tồn tại';
}
