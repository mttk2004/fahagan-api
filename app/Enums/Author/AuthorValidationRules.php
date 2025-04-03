<?php

namespace App\Enums\Author;

enum AuthorValidationRules
{
    case NAME;
    case BIOGRAPHY;
    case IMAGE_URL;
    case BOOK_ID;

    public function rules(): array
    {
        return match($this) {
            self::NAME => ['required', 'string', 'max:255'],
            self::BIOGRAPHY => ['required', 'string'],
            self::IMAGE_URL => ['required', 'string', 'url'],
            self::BOOK_ID => ['required', 'integer', 'exists:books,id'],
        };
    }
}
