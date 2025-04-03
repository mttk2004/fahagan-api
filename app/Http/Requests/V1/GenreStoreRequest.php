<?php

namespace App\Http\Requests\V1;

use App\Enums\Genre\GenreValidationMessages;
use App\Enums\Genre\GenreValidationRules;
use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Utils\AuthUtils;

class GenreStoreRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        return [
            'data.attributes.name' => GenreValidationRules::NAME->rules(),
            'data.attributes.slug' => GenreValidationRules::SLUG->rules(),
            'data.attributes.description' => GenreValidationRules::DESCRIPTION->rules(),
        ];
    }

    public function messages(): array
    {
        return [
            'data.attributes.name.required' => GenreValidationMessages::NAME_REQUIRED->message(),
            'data.attributes.name.string' => GenreValidationMessages::NAME_STRING->message(),
            'data.attributes.name.max' => GenreValidationMessages::NAME_MAX->message(),
            'data.attributes.name.unique' => GenreValidationMessages::NAME_UNIQUE->message(),

            'data.attributes.slug.required' => GenreValidationMessages::SLUG_REQUIRED->message(),
            'data.attributes.slug.string' => GenreValidationMessages::SLUG_STRING->message(),
            'data.attributes.slug.max' => GenreValidationMessages::SLUG_MAX->message(),
            'data.attributes.slug.unique' => GenreValidationMessages::SLUG_UNIQUE->message(),

            'data.attributes.description.string' => GenreValidationMessages::DESCRIPTION_STRING->message(),
            'data.attributes.description.max' => GenreValidationMessages::DESCRIPTION_MAX->message(),
        ];
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('create_genres');
    }
}
