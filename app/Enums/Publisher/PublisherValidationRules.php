<?php

namespace App\Enums\Publisher;

use Illuminate\Validation\Rule;

enum PublisherValidationRules
{
    case NAME;
    case BIOGRAPHY;

    public function rules(): array
    {
        return match($this) {
            self::NAME => ['required', 'string', 'max:255'],
            self::BIOGRAPHY => ['required', 'string'],
        };
    }

    /**
     * Lấy quy tắc validation cho name với kiểm tra unique
     * và loại trừ các bản ghi đã bị soft delete
     */
    public static function getNameRuleWithUnique(): array
    {
        $uniqueRule = Rule::unique('publishers', 'name')
            ->whereNull('deleted_at');

        return array_merge(
            self::NAME->rules(),
            [$uniqueRule]
        );
    }
}
