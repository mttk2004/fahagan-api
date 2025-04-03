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
            'name' => GenreValidationRules::NAME->rules(),
            'slug' => GenreValidationRules::SLUG->rules(),
            'description' => GenreValidationRules::DESCRIPTION->rules(),
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => GenreValidationMessages::NAME_REQUIRED->message(),
            'name.string' => GenreValidationMessages::NAME_STRING->message(),
            'name.max' => GenreValidationMessages::NAME_MAX->message(),
            'name.unique' => GenreValidationMessages::NAME_UNIQUE->message(),

            'slug.required' => GenreValidationMessages::SLUG_REQUIRED->message(),
            'slug.string' => GenreValidationMessages::SLUG_STRING->message(),
            'slug.max' => GenreValidationMessages::SLUG_MAX->message(),
            'slug.unique' => GenreValidationMessages::SLUG_UNIQUE->message(),

            'description.string' => GenreValidationMessages::DESCRIPTION_STRING->message(),
            'description.max' => GenreValidationMessages::DESCRIPTION_MAX->message(),
        ];
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('create_genres');
    }
}
