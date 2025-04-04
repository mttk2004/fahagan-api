<?php

namespace App\Enums\Supplier;

use App\Abstracts\BaseValidationRules;
use App\Traits\HasUniqueRules;

enum SupplierValidationRules
{
    use BaseValidationRules;

    case NAME;
    case PHONE;
    case EMAIL;
    case CITY;
    case DISTRICT;
    case WARD;
    case ADDRESS_LINE;
    case BOOK_ID;

    public function rules(): array
    {
        return match ($this) {
            self::NAME => ['required', 'string', 'max:255'],
            self::PHONE => ['sometimes', 'nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]*$/'],
            self::EMAIL => ['sometimes', 'nullable', 'string', 'email', 'max:255'],
            self::CITY => ['sometimes', 'nullable', 'string', 'max:255'],
            self::DISTRICT => ['sometimes', 'nullable', 'string', 'max:255'],
            self::WARD => ['sometimes', 'nullable', 'string', 'max:255'],
            self::ADDRESS_LINE => ['sometimes', 'nullable', 'string', 'max:255'],
            self::BOOK_ID => ['required', 'integer', 'exists:books,id'],
        };
    }

    /**
     * Lấy quy tắc validation cho name với kiểm tra unique
     */
    public static function getNameRuleWithUnique(?string $supplierId = null): array
    {
        return array_merge(
            self::NAME->rules(),
            [HasUniqueRules::createUniqueRule('suppliers', 'name', $supplierId)]
        );
    }
}
