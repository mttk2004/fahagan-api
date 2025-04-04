<?php

namespace App\Http\Requests\V1;

use App\Enums\Address\AddressValidationMessages;
use App\Enums\Address\AddressValidationRules;
use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Traits\HasApiJsonValidation;
use App\Traits\HasUpdateRules;
use App\Traits\HasRequestFormat;

class AddressUpdateRequest extends BaseRequest implements HasValidationMessages
{
    use HasApiJsonValidation;
    use HasUpdateRules;
    use HasRequestFormat;

    /**
     * Chuẩn bị dữ liệu trước khi validation
     */
    protected function prepareForValidation(): void
    {
        // Chuyển đổi từ direct format sang JSON:API format
        // Address không có relationships, được phép sử dụng direct format
        $this->convertToJsonApiFormat([
            'name',
            'phone',
            'city',
            'district',
            'ward',
            'address_line'
        ]);
    }

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
