<?php

namespace App\Http\Requests\V1;

use App\Enums\Supplier\SupplierValidationMessages;
use App\Enums\Supplier\SupplierValidationRules;
use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Traits\HasApiJsonValidation;
use App\Utils\AuthUtils;

class SupplierStoreRequest extends BaseRequest implements HasValidationMessages
{
    use HasApiJsonValidation;

    public function rules(): array
    {
        $attributesRules = $this->mapAttributesRules([
            'name' => SupplierValidationRules::getNameRuleWithUnique(),
            'phone' => SupplierValidationRules::PHONE->rules(),
            'email' => SupplierValidationRules::EMAIL->rules(),
            'city' => SupplierValidationRules::CITY->rules(),
            'district' => SupplierValidationRules::DISTRICT->rules(),
            'ward' => SupplierValidationRules::WARD->rules(),
            'address_line' => SupplierValidationRules::ADDRESS_LINE->rules(),
        ]);

        $relationshipsRules = [
            'data.relationships.books.data.*.id' => SupplierValidationRules::BOOK_ID->rules(),
        ];

        return array_merge($attributesRules, $relationshipsRules);
    }

    public function messages(): array
    {
        return SupplierValidationMessages::getJsonApiMessages();
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('create_suppliers');
    }
}
