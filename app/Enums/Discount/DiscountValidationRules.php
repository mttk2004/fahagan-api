<?php

namespace App\Enums\Discount;

use App\Traits\HasUniqueRules;
use App\Traits\HasUpdateRules;

enum DiscountValidationRules
{
    use HasUpdateRules;
    use HasUniqueRules;

    case NAME;
    case DISCOUNT_TYPE;
    case DISCOUNT_VALUE;
    case START_DATE;
    case END_DATE;
    case TARGET_TYPE;
    case TARGET_ID;
    case TARGET_ARRAY;

    public function rules(): array
    {
        return match($this) {
            self::NAME => ['required', 'string', 'max:255'],
            self::DISCOUNT_TYPE => ['required', 'string', 'in:percent,fixed'],
            self::DISCOUNT_VALUE => ['required', 'numeric', 'min:0'],
            self::START_DATE => ['required', 'date', 'before_or_equal:end_date'],
            self::END_DATE => ['required', 'date', 'after_or_equal:start_date'],
            self::TARGET_TYPE => ['required', 'string', 'in:book,author,publisher,genre'],
            self::TARGET_ID => ['required', 'integer'],
            self::TARGET_ARRAY => ['required', 'array'],
        };
    }

    /**
     * Lấy quy tắc validation cho name với kiểm tra unique
     * và loại trừ các bản ghi đã bị soft delete
     */
    public static function getNameRuleWithUnique(?string $id = null): array
    {
        return self::addUniqueRule(
            self::NAME->rules(),
            'discounts',
            'name',
            $id
        );
    }
}
