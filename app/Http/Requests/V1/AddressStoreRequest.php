<?php

namespace App\Http\Requests\V1;

use App\Enums\Address\AddressValidationMessages;
use App\Enums\Address\AddressValidationRules;
use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Traits\HasApiJsonValidation;

class AddressStoreRequest extends BaseRequest implements HasValidationMessages
{
    use HasApiJsonValidation;

    public function rules(): array
    {
        return $this->mapAttributesRules([
            'name' => AddressValidationRules::NAME->rules(),
            'phone' => AddressValidationRules::PHONE->rules(),
            'city' => AddressValidationRules::CITY->rules(),
            'district' => AddressValidationRules::DISTRICT->rules(),
            'ward' => AddressValidationRules::WARD->rules(),
            'address_line' => AddressValidationRules::ADDRESS_LINE->rules(),
        ]);
    }

    public function messages(): array
    {
        return AddressValidationMessages::getJsonApiMessages();
    }

    public function authorize(): bool
    {
        return true;
    }
}
