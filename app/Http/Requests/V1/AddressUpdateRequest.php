<?php

namespace App\Http\Requests\V1;

use App\Enums\Address\AddressValidationMessages;
use App\Enums\Address\AddressValidationRules;
use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Traits\HasApiJsonValidation;
use App\Traits\HasUpdateRules;

class AddressUpdateRequest extends BaseRequest implements HasValidationMessages
{
    use HasApiJsonValidation;
    use HasUpdateRules;

    public function rules(): array
    {
        $attributesRules = $this->mapAttributesRules([
            'name' => HasUpdateRules::transformToUpdateRules(AddressValidationRules::NAME->rules()),
            'phone' => HasUpdateRules::transformToUpdateRules(AddressValidationRules::PHONE->rules()),
            'city' => HasUpdateRules::transformToUpdateRules(AddressValidationRules::CITY->rules()),
            'district' => HasUpdateRules::transformToUpdateRules(AddressValidationRules::DISTRICT->rules()),
            'ward' => HasUpdateRules::transformToUpdateRules(AddressValidationRules::WARD->rules()),
            'address_line' => HasUpdateRules::transformToUpdateRules(AddressValidationRules::ADDRESS_LINE->rules()),
        ]);

        return $attributesRules;
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
