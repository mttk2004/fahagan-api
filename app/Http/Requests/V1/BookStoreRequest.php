<?php

namespace App\Http\Requests\V1;

use App\Enums\Book\BookValidationMessages;
use App\Enums\Book\BookValidationRules;
use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Utils\AuthUtils;

class BookStoreRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        $edition = request('data.attributes.edition');

        return [
            'data.attributes.title' => [BookValidationRules::getTitleRuleWithUnique($edition)],
            'data.attributes.description' => [BookValidationRules::DESCRIPTION->value],
            'data.attributes.price' => [BookValidationRules::PRICE->value],
            'data.attributes.edition' => [BookValidationRules::EDITION->value],
            'data.attributes.pages' => [BookValidationRules::PAGES->value],
            'data.attributes.image_url' => [BookValidationRules::IMAGE_URL->value],
            'data.attributes.publication_date' => [BookValidationRules::PUBLICATION_DATE->value],
            'data.relationships.authors.data.*.id' => [BookValidationRules::AUTHOR_ID->value],
            'data.relationships.genres.data.*.id' => [BookValidationRules::GENRE_ID->value],
            'data.relationships.publisher.id' => [BookValidationRules::PUBLISHER_ID->value],
        ];
    }

    public function messages(): array
    {
        return [
            'data.attributes.title.required' => BookValidationMessages::TITLE_REQUIRED->value,
            'data.attributes.title.string' => BookValidationMessages::TITLE_STRING->value,
            'data.attributes.title.max' => BookValidationMessages::TITLE_MAX->value,
            'data.attributes.title.unique' => BookValidationMessages::TITLE_UNIQUE->value,

            'data.attributes.description.required' => BookValidationMessages::DESCRIPTION_REQUIRED->value,
            'data.attributes.description.string' => BookValidationMessages::DESCRIPTION_STRING->value,

            'data.attributes.price.required' => BookValidationMessages::PRICE_REQUIRED->value,
            'data.attributes.price.numeric' => BookValidationMessages::PRICE_NUMERIC->value,
            'data.attributes.price.min' => BookValidationMessages::PRICE_MIN->value,
            'data.attributes.price.max' => BookValidationMessages::PRICE_MAX->value,

            'data.attributes.edition.required' => BookValidationMessages::EDITION_REQUIRED->value,
            'data.attributes.edition.integer' => BookValidationMessages::EDITION_INTEGER->value,
            'data.attributes.edition.min' => BookValidationMessages::EDITION_MIN->value,
            'data.attributes.edition.max' => BookValidationMessages::EDITION_MAX->value,

            'data.attributes.pages.required' => BookValidationMessages::PAGES_REQUIRED->value,
            'data.attributes.pages.integer' => BookValidationMessages::PAGES_INTEGER->value,
            'data.attributes.pages.min' => BookValidationMessages::PAGES_MIN->value,
            'data.attributes.pages.max' => BookValidationMessages::PAGES_MAX->value,

            'data.attributes.image_url.string' => BookValidationMessages::IMAGE_URL_STRING->value,

            'data.attributes.publication_date.required' => BookValidationMessages::PUBLICATION_DATE_REQUIRED->value,
            'data.attributes.publication_date.date' => BookValidationMessages::PUBLICATION_DATE_DATE->value,
            'data.attributes.publication_date.before' => BookValidationMessages::PUBLICATION_DATE_BEFORE->value,

            'data.relationships.authors.data.*.id.required' => BookValidationMessages::AUTHOR_ID_REQUIRED->value,
            'data.relationships.authors.data.*.id.integer' => BookValidationMessages::AUTHOR_ID_INTEGER->value,
            'data.relationships.authors.data.*.id.exists' => BookValidationMessages::AUTHOR_ID_EXISTS->value,

            'data.relationships.genres.data.*.id.required' => BookValidationMessages::GENRE_ID_REQUIRED->value,
            'data.relationships.genres.data.*.id.integer' => BookValidationMessages::GENRE_ID_INTEGER->value,
            'data.relationships.genres.data.*.id.exists' => BookValidationMessages::GENRE_ID_EXISTS->value,

            'data.relationships.publisher.id.required' => BookValidationMessages::PUBLISHER_ID_REQUIRED->value,
            'data.relationships.publisher.id.integer' => BookValidationMessages::PUBLISHER_ID_INTEGER->value,
            'data.relationships.publisher.id.exists' => BookValidationMessages::PUBLISHER_ID_EXISTS->value,
        ];
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('create_books');
    }
}
