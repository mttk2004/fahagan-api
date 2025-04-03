<?php

namespace App\Http\Requests\V1;

use App\Enums\Author\AuthorValidationMessages;
use App\Enums\Author\AuthorValidationRules;
use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Utils\AuthUtils;

class AuthorStoreRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        return [
            'data.attributes.name' => AuthorValidationRules::NAME->rules(),
            'data.attributes.biography' => AuthorValidationRules::BIOGRAPHY->rules(),
            'data.attributes.image_url' => AuthorValidationRules::IMAGE_URL->rules(),
            'data.relationships.books.data.*.id' => AuthorValidationRules::BOOK_ID->rules(),
        ];
    }

    public function messages(): array
    {
        return [
            'data.attributes.name.required' => AuthorValidationMessages::NAME_REQUIRED->message(),
            'data.attributes.name.string' => AuthorValidationMessages::NAME_STRING->message(),
            'data.attributes.name.max' => AuthorValidationMessages::NAME_MAX->message(),

            'data.attributes.biography.required' => AuthorValidationMessages::BIOGRAPHY_REQUIRED->message(),
            'data.attributes.biography.string' => AuthorValidationMessages::BIOGRAPHY_STRING->message(),

            'data.attributes.image_url.required' => AuthorValidationMessages::IMAGE_URL_REQUIRED->message(),
            'data.attributes.image_url.string' => AuthorValidationMessages::IMAGE_URL_STRING->message(),
            'data.attributes.image_url.url' => AuthorValidationMessages::IMAGE_URL_URL->message(),

            'data.relationships.books.data.*.id.required' => AuthorValidationMessages::BOOK_ID_REQUIRED->message(),
            'data.relationships.books.data.*.id.integer' => AuthorValidationMessages::BOOK_ID_INTEGER->message(),
            'data.relationships.books.data.*.id.exists' => AuthorValidationMessages::BOOK_ID_EXISTS->message(),
        ];
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('create_authors');
    }
}
