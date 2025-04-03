<?php

namespace App\Enums\Discount;

use Illuminate\Validation\Rule;

enum DiscountValidationRules
{
    case NAME;
    case DISCOUNT_TYPE;
    case DISCOUNT_VALUE;
    case START_DATE;
    case END_DATE;

    public function rules(): array
    {
        return match($this) {
            self::NAME => ['required', 'string', 'max:255'],
            self::DISCOUNT_TYPE => ['required', 'string', 'in:percent,fixed'],
            self::DISCOUNT_VALUE => ['required', 'numeric', 'min:0'],
            self::START_DATE => ['required', 'date', 'before_or_equal:end_date'],
            self::END_DATE => ['required', 'date', 'after_or_equal:start_date'],
        };
    }

    /**
     * Lấy quy tắc validation cho name với kiểm tra unique
     * và loại trừ các bản ghi đã bị soft delete
     */
    public static function getNameRuleWithUnique(?string $id = null): array
    {
        $uniqueRule = Rule::unique('discounts', 'name')
            ->whereNull('deleted_at');

        if ($id !== null) {
            $uniqueRule->ignore($id);
        }

        return array_merge(
            self::NAME->rules(),
            [$uniqueRule]
        );
    }
}
