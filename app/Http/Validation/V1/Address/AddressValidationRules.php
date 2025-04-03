<?php

namespace App\Http\Validation\V1\Address;

class AddressValidationRules
{
    public static function getCreationRules(): array
    {
        return [
            'name' => 'required|string',
            'phone' => [
                'required',
                'string',
                'regex:/^0[35789][0-9]{8}$/',
            ],
            'city' => 'required|string',
            'district' => 'required|string',
            'ward' => 'required|string',
            'address_line' => 'required|string',
        ];
    }

    public static function getUpdateRules(): array
    {
        return [
            'name' => 'sometimes|string',
            'phone' => [
                'sometimes',
                'string',
                'regex:/^0[35789][0-9]{8}$/',
            ],
            'city' => 'sometimes|string',
            'district' => 'sometimes|string',
            'ward' => 'sometimes|string',
            'address_line' => 'sometimes|string',
        ];
    }
}
