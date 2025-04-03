<?php

namespace App\Enums\CartItem;

enum CartItemValidationRules
{
    case BOOK_ID;
    case QUANTITY;

    public function rules(): array
    {
        return match($this) {
            self::BOOK_ID => ['required', 'integer', 'exists:books,id'],
            self::QUANTITY => ['required', 'integer', 'min:1'],
        };
    }
}
