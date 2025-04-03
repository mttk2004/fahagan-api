<?php

namespace App\Enums\Book;

use Illuminate\Validation\Rule;

enum BookValidationRules
{
    case TITLE;
    case DESCRIPTION;
    case PRICE;
    case EDITION;
    case PAGES;
    case IMAGE_URL;
    case PUBLICATION_DATE;
    case AUTHOR_ID;
    case GENRE_ID;
    case PUBLISHER_ID;

    public function rules(): array
    {
        return match($this) {
            self::TITLE => ['required', 'string', 'max:255'],
            self::DESCRIPTION => ['required', 'string'],
            self::PRICE => ['required', 'numeric', 'min:200000', 'max:10000000'],
            self::EDITION => ['required', 'integer', 'min:1', 'max:30'],
            self::PAGES => ['required', 'integer', 'min:50', 'max:5000'],
            self::IMAGE_URL => ['required', 'string', 'url'],
            self::PUBLICATION_DATE => ['required', 'date', 'before:today'],
            self::AUTHOR_ID => ['required', 'integer', 'exists:authors,id'],
            self::GENRE_ID => ['required', 'integer', 'exists:genres,id'],
            self::PUBLISHER_ID => ['required', 'integer', 'exists:publishers,id'],
        };
    }

    /**
     * Lấy quy tắc validation cho title với kiểm tra unique
     * và loại trừ các bản ghi đã bị soft delete
     */
    public static function getTitleRuleWithUnique(string $edition = null): array
    {
        $uniqueRule = Rule::unique('books', 'title')
            ->whereNull('deleted_at');

        if ($edition !== null) {
            $uniqueRule->where('edition', $edition);
        }

        return array_merge(
            self::TITLE->rules(),
            [$uniqueRule]
        );
    }

    /**
     * Lấy quy tắc validation cho edition với kiểm tra unique
     * và loại trừ các bản ghi đã bị soft delete
     */
    public static function getEditionRuleWithUnique(string $title = null): array
    {
        $uniqueRule = Rule::unique('books', 'edition')
            ->whereNull('deleted_at');

        if ($title !== null) {
            $uniqueRule->where('title', $title);
        }

        return array_merge(
            self::EDITION->rules(),
            [$uniqueRule]
        );
    }
}
