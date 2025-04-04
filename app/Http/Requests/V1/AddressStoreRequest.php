<?php

namespace App\Http\Requests\V1;

use App\Enums\Address\AddressValidationMessages;
use App\Enums\Address\AddressValidationRules;
use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Traits\HasApiJsonValidation;
use App\Traits\HasRequestFormat;

class AddressStoreRequest extends BaseRequest implements HasValidationMessages
{
    use HasApiJsonValidation;
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
