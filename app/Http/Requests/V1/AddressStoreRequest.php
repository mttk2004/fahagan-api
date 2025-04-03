<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseRequest;
use App\Http\Validation\V1\Address\AddressValidationMessages;
use App\Http\Validation\V1\Address\AddressValidationRules;
use App\Interfaces\HasValidationMessages;

class AddressStoreRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        return AddressValidationRules::getCreationRules();
    }

    public function messages(): array
    {
        return AddressValidationMessages::getMessages();
    }

    public function authorize(): bool
    {
        return true;
    }
}
