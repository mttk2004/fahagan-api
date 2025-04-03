<?php

namespace App\Enums\Genre;

use App\Models\Genre;
use Illuminate\Validation\Rule;

enum GenreValidationRules
{
    case NAME;
    case SLUG;
    case DESCRIPTION;

    public function rules(): array
    {
        return match($this) {
            self::NAME => $this->getNameRuleWithUnique(),
            self::SLUG => $this->getSlugRuleWithUnique(),
            self::DESCRIPTION => ['sometimes', 'nullable', 'string', 'max:500'],
        };
    }

    public function getNameRuleWithUnique(?int $genreId = null): array
    {
        return [
            'required',
            'string',
            'max:50',
            Rule::unique('genres', 'name')->ignore($genreId)->whereNull('deleted_at'),
        ];
    }

    public function getSlugRuleWithUnique(?int $genreId = null): array
    {
        return [
            'required',
            'string',
            'max:100',
            Rule::unique('genres', 'slug')->ignore($genreId)->whereNull('deleted_at'),
        ];
    }
}
