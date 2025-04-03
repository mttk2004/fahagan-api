<?php

namespace App\Http\Requests\V1;

use App\Enums\Publisher\PublisherValidationMessages;
use App\Enums\Publisher\PublisherValidationRules;
use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Utils\AuthUtils;

class PublisherStoreRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        return [
            'data.attributes.name' => PublisherValidationRules::getNameRuleWithUnique(),
            'data.attributes.biography' => PublisherValidationRules::BIOGRAPHY->rules(),
        ];
    }

    public function messages(): array
    {
        return [
            'data.attributes.name.required' => PublisherValidationMessages::NAME_REQUIRED->message(),
            'data.attributes.name.string' => PublisherValidationMessages::NAME_STRING->message(),
            'data.attributes.name.max' => PublisherValidationMessages::NAME_MAX->message(),
            'data.attributes.name.unique' => PublisherValidationMessages::NAME_UNIQUE->message(),
            'data.attributes.biography.required' => PublisherValidationMessages::BIOGRAPHY_REQUIRED->message(),
            'data.attributes.biography.string' => PublisherValidationMessages::BIOGRAPHY_STRING->message(),
        ];
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('create_publishers');
    }
}
