<?php

namespace App\Http\Requests\V1;

use App\Enums\Supplier\SupplierValidationMessages;
use App\Enums\Supplier\SupplierValidationRules;
use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Utils\AuthUtils;

class SupplierStoreRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        return [
            'name' => SupplierValidationRules::NAME->rules(),
            'phone' => SupplierValidationRules::PHONE->rules(),
            'email' => SupplierValidationRules::EMAIL->rules(),
            'city' => SupplierValidationRules::CITY->rules(),
            'district' => SupplierValidationRules::DISTRICT->rules(),
            'ward' => SupplierValidationRules::WARD->rules(),
            'address_line' => SupplierValidationRules::ADDRESS_LINE->rules(),
            'data.relationships.books.data.*.id' => ['required', 'integer', 'exists:books,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => SupplierValidationMessages::NAME_REQUIRED->message(),
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

            'data.relationships.books.data.*.id.required' => 'ID sách là trường bắt buộc.',
            'data.relationships.books.data.*.id.integer' => 'ID sách nên là một số nguyên.',
            'data.relationships.books.data.*.id.exists' => 'ID sách không tồn tại.',
        ];
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('create_suppliers');
    }
}
