<?php

namespace App\Enums\Publisher;

use App\Abstracts\BaseValidationRules;
use App\Traits\HasUniqueRules;

enum PublisherValidationRules
{
    use BaseValidationRules;

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
    public static function getNameRuleWithUnique(?string $id = null): array
    {
        return array_merge(
            self::NAME->rules(),
            [HasUniqueRules::createUniqueRule('publishers', 'name', $id)]
        );
    }
}
