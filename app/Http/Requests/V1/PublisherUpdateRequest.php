<?php

namespace App\Http\Requests\V1;

use App\Enums\Publisher\PublisherValidationMessages;
use App\Enums\Publisher\PublisherValidationRules;
use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Utils\AuthUtils;
use Illuminate\Validation\Rule;

class PublisherUpdateRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        $publisherId = $this->route('publisher');

        return [
            'data.attributes.name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('publishers', 'name')
                    ->whereNull('deleted_at')
                    ->ignore($publisherId)
            ],
            'data.attributes.biography' => ['sometimes', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'data.attributes.name.string' => PublisherValidationMessages::NAME_STRING->message(),
            'data.attributes.name.max' => PublisherValidationMessages::NAME_MAX->message(),
            'data.attributes.name.unique' => PublisherValidationMessages::NAME_UNIQUE->message(),
            'data.attributes.biography.string' => PublisherValidationMessages::BIOGRAPHY_STRING->message(),
        ];
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('edit_publishers');
    }
}
