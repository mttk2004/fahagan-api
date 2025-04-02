<?php

namespace App\Enums\Book;

enum BookValidationRules: string
{
    case TITLE = 'required|string|max:255';
    case DESCRIPTION = 'required|string';
    case PRICE = 'required|numeric|min:200000|max:10000000';
    case EDITION = 'required|integer|min:1|max:30';
    case PAGES = 'required|integer|min:50|max:5000';
    case IMAGE_URL = 'sometimes|string';
    case PUBLICATION_DATE = 'required|date|before:today';
    case AUTHOR_ID = 'required|integer|exists:authors,id';
    case GENRE_ID = 'required|integer|exists:genres,id';
    case PUBLISHER_ID = 'required|integer|exists:publishers,id';

    public static function getTitleRuleWithUnique(string $edition = null): string
    {
        return self::TITLE->value . '|unique:books,title,NULL,id,edition,' . ($edition ?? 'NULL');
    }
}
