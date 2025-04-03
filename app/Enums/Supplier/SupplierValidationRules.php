<?php

namespace App\Enums\Supplier;

use Illuminate\Validation\Rule;

enum SupplierValidationRules
{
    case NAME;
    case PHONE;
    case EMAIL;
    case CITY;
    case DISTRICT;
    case WARD;
    case ADDRESS_LINE;

    public function rules(string|int|null $id = null): array
    {
        return match ($this) {
            self::NAME => [
                'required',
                'string',
                'max:255',
                Rule::unique('suppliers', 'name')->ignore($id)->whereNull('deleted_at'),
            ],
            self::PHONE => [
                'sometimes',
                'nullable',
                'string',
                'max:20',
                'regex:/^[0-9+\-\s()]*$/',
            ],
            self::EMAIL => [
                'sometimes',
                'nullable',
                'string',
                'email',
                'max:255',
            ],
            self::CITY => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],
            self::DISTRICT => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],
            self::WARD => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],
            self::ADDRESS_LINE => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],
        };
    }
}
