<?php

namespace App\Http\Requests\V1;

use App\Enums\Supplier\SupplierValidationMessages;
use App\Enums\Supplier\SupplierValidationRules;
use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Utils\AuthUtils;

class SupplierUpdateRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', ...(SupplierValidationRules::NAME->rules(request('id')))],
            'phone' => ['sometimes', ...(SupplierValidationRules::PHONE->rules())],
            'email' => ['sometimes', ...(SupplierValidationRules::EMAIL->rules())],
            'city' => ['sometimes', ...(SupplierValidationRules::CITY->rules())],
            'district' => ['sometimes', ...(SupplierValidationRules::DISTRICT->rules())],
            'ward' => ['sometimes', ...(SupplierValidationRules::WARD->rules())],
            'address_line' => ['sometimes', ...(SupplierValidationRules::ADDRESS_LINE->rules())],
            'books' => ['sometimes', 'array'],
            'books.*' => ['sometimes', 'integer', 'exists:books,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => SupplierValidationMessages::NAME_STRING->message(),
            'name.max' => SupplierValidationMessages::NAME_MAX->message(),
            'name.unique' => SupplierValidationMessages::NAME_UNIQUE->message(),

            'phone.string' => SupplierValidationMessages::PHONE_STRING->message(),
            'phone.max' => SupplierValidationMessages::PHONE_MAX->message(),
            'phone.regex' => SupplierValidationMessages::PHONE_REGEX->message(),

            'email.string' => SupplierValidationMessages::EMAIL_STRING->message(),
            'email.email' => SupplierValidationMessages::EMAIL_EMAIL->message(),
            'email.max' => SupplierValidationMessages::EMAIL_MAX->message(),

            'city.string' => SupplierValidationMessages::CITY_STRING->message(),
            'city.max' => SupplierValidationMessages::CITY_MAX->message(),

            'district.string' => SupplierValidationMessages::DISTRICT_STRING->message(),
            'district.max' => SupplierValidationMessages::DISTRICT_MAX->message(),

            'ward.string' => SupplierValidationMessages::WARD_STRING->message(),
            'ward.max' => SupplierValidationMessages::WARD_MAX->message(),

            'address_line.string' => SupplierValidationMessages::ADDRESS_LINE_STRING->message(),
            'address_line.max' => SupplierValidationMessages::ADDRESS_LINE_MAX->message(),

            'books.array' => 'Danh sách sách phải là một mảng.',
            'books.*.integer' => 'ID sách phải là một số nguyên.',
            'books.*.exists' => 'ID sách không tồn tại.',
        ];
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('edit_suppliers');
    }
}
