<?php

namespace App\Enums\CartItem;

enum CartItemValidationMessages
{
    case BOOK_ID_REQUIRED;
    case BOOK_ID_INTEGER;
    case BOOK_ID_EXISTS;

    case QUANTITY_REQUIRED;
    case QUANTITY_INTEGER;
    case QUANTITY_MIN;

    public function message(): string
    {
        return match($this) {
            self::BOOK_ID_REQUIRED => 'ID sách là trường bắt buộc.',
            self::BOOK_ID_INTEGER => 'ID sách nên là một số nguyên.',
            self::BOOK_ID_EXISTS => 'ID sách không tồn tại.',

            self::QUANTITY_REQUIRED => 'Số lượng là trường bắt buộc.',
            self::QUANTITY_INTEGER => 'Số lượng nên là một số nguyên.',
            self::QUANTITY_MIN => 'Số lượng nên lớn hơn hoặc bằng 1.',
        };
    }
}
