<?php

namespace App\Http\Requests\V1;

use App\Enums\Author\AuthorValidationMessages;
use App\Enums\Author\AuthorValidationRules;
use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Utils\AuthUtils;

class AuthorUpdateRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        return [
            'data.attributes.name' => array_merge(
                ['sometimes'],
                array_filter(AuthorValidationRules::NAME->rules(), fn ($rule) => $rule !== 'required')
            ),
            'data.attributes.biography' => array_merge(
                ['sometimes'],
                array_filter(AuthorValidationRules::BIOGRAPHY->rules(), fn ($rule) => $rule !== 'required')
            ),
            'data.attributes.image_url' => array_merge(
                ['sometimes'],
                array_filter(AuthorValidationRules::IMAGE_URL->rules(), fn ($rule) => $rule !== 'required')
            ),
            'data.relationships.books.data.*.id' => array_merge(
                ['sometimes'],
                array_filter(AuthorValidationRules::BOOK_ID->rules(), fn ($rule) => $rule !== 'required')
            ),
        ];
    }

    public function messages(): array
    {
        return [
            'data.attributes.name.string' => AuthorValidationMessages::NAME_STRING->message(),
            'data.attributes.name.max' => AuthorValidationMessages::NAME_MAX->message(),

            'data.attributes.biography.string' => AuthorValidationMessages::BIOGRAPHY_STRING->message(),

            'data.attributes.image_url.string' => AuthorValidationMessages::IMAGE_URL_STRING->message(),
            'data.attributes.image_url.url' => AuthorValidationMessages::IMAGE_URL_URL->message(),

            'data.relationships.books.data.*.id.integer' => AuthorValidationMessages::BOOK_ID_INTEGER->message(),
            'data.relationships.books.data.*.id.exists' => AuthorValidationMessages::BOOK_ID_EXISTS->message(),
        ];
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('edit_authors');
    }
}
