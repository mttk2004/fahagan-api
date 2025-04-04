<?php

namespace App\Enums\Genre;

use App\Abstracts\BaseValidationRules;
use App\Traits\HasUniqueRules;

enum GenreValidationRules
{
    use BaseValidationRules;

    case NAME;
    case SLUG;
    case DESCRIPTION;

    public function rules(): array
    {
        return match($this) {
            self::NAME => ['required', 'string', 'max:50'],
            self::SLUG => ['required', 'string', 'max:100'],
            self::DESCRIPTION => ['sometimes', 'nullable', 'string', 'max:500'],
        };
    }

    public static function getNameRuleWithUnique(?int $genreId = null): array
    {
        return array_merge(
            self::NAME->rules(),
            [HasUniqueRules::createUniqueRule('genres', 'name', $genreId ? (string)$genreId : null)]
        );
    }

    public static function getSlugRuleWithUnique(?int $genreId = null): array
    {
        return array_merge(
            self::SLUG->rules(),
            [HasUniqueRules::createUniqueRule('genres', 'slug', $genreId ? (string)$genreId : null)]
        );
    }
}
