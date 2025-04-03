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
        $title = request('data.attributes.title');
        $edition = request('data.attributes.edition');

        return [
            'data.attributes.title' => BookValidationRules::getTitleRuleWithUnique($edition),
            'data.attributes.description' => BookValidationRules::DESCRIPTION->rules(),
            'data.attributes.price' => BookValidationRules::PRICE->rules(),
            'data.attributes.edition' => BookValidationRules::getEditionRuleWithUnique($title),
            'data.attributes.pages' => BookValidationRules::PAGES->rules(),
            'data.attributes.image_url' => BookValidationRules::IMAGE_URL->rules(),
            'data.attributes.publication_date' => BookValidationRules::PUBLICATION_DATE->rules(),
            'data.relationships.authors.data.*.id' => BookValidationRules::AUTHOR_ID->rules(),
            'data.relationships.genres.data.*.id' => BookValidationRules::GENRE_ID->rules(),
            'data.relationships.publisher.id' => BookValidationRules::PUBLISHER_ID->rules(),
        ];
    }

    public function messages(): array
    {
        return [
            'data.attributes.title.required' => BookValidationMessages::TITLE_REQUIRED->message(),
            'data.attributes.title.string' => BookValidationMessages::TITLE_STRING->message(),
            'data.attributes.title.max' => BookValidationMessages::TITLE_MAX->message(),
            'data.attributes.title.unique' => BookValidationMessages::TITLE_UNIQUE->message(),

            'data.attributes.description.required' => BookValidationMessages::DESCRIPTION_REQUIRED->message(),
            'data.attributes.description.string' => BookValidationMessages::DESCRIPTION_STRING->message(),

            'data.attributes.price.required' => BookValidationMessages::PRICE_REQUIRED->message(),
            'data.attributes.price.numeric' => BookValidationMessages::PRICE_NUMERIC->message(),
            'data.attributes.price.min' => BookValidationMessages::PRICE_MIN->message(),
            'data.attributes.price.max' => BookValidationMessages::PRICE_MAX->message(),

            'data.attributes.edition.required' => BookValidationMessages::EDITION_REQUIRED->message(),
            'data.attributes.edition.integer' => BookValidationMessages::EDITION_INTEGER->message(),
            'data.attributes.edition.min' => BookValidationMessages::EDITION_MIN->message(),
            'data.attributes.edition.max' => BookValidationMessages::EDITION_MAX->message(),
            'data.attributes.edition.unique' => BookValidationMessages::TITLE_UNIQUE->message(),

            'data.attributes.pages.required' => BookValidationMessages::PAGES_REQUIRED->message(),
            'data.attributes.pages.integer' => BookValidationMessages::PAGES_INTEGER->message(),
            'data.attributes.pages.min' => BookValidationMessages::PAGES_MIN->message(),
            'data.attributes.pages.max' => BookValidationMessages::PAGES_MAX->message(),

            'data.attributes.image_url.required' => BookValidationMessages::IMAGE_URL_REQUIRED->message(),
            'data.attributes.image_url.string' => BookValidationMessages::IMAGE_URL_STRING->message(),
            'data.attributes.image_url.url' => BookValidationMessages::IMAGE_URL_URL->message(),

            'data.attributes.publication_date.required' => BookValidationMessages::PUBLICATION_DATE_REQUIRED->message(),
            'data.attributes.publication_date.date' => BookValidationMessages::PUBLICATION_DATE_DATE->message(),
            'data.attributes.publication_date.before' => BookValidationMessages::PUBLICATION_DATE_BEFORE->message(),

            'data.relationships.authors.data.*.id.required' => BookValidationMessages::AUTHOR_ID_REQUIRED->message(),
            'data.relationships.authors.data.*.id.integer' => BookValidationMessages::AUTHOR_ID_INTEGER->message(),
            'data.relationships.authors.data.*.id.exists' => BookValidationMessages::AUTHOR_ID_EXISTS->message(),

            'data.relationships.genres.data.*.id.required' => BookValidationMessages::GENRE_ID_REQUIRED->message(),
            'data.relationships.genres.data.*.id.integer' => BookValidationMessages::GENRE_ID_INTEGER->message(),
            'data.relationships.genres.data.*.id.exists' => BookValidationMessages::GENRE_ID_EXISTS->message(),

            'data.relationships.publisher.id.required' => BookValidationMessages::PUBLISHER_ID_REQUIRED->message(),
            'data.relationships.publisher.id.integer' => BookValidationMessages::PUBLISHER_ID_INTEGER->message(),
            'data.relationships.publisher.id.exists' => BookValidationMessages::PUBLISHER_ID_EXISTS->message(),
        ];
    }

    public function authorize(): bool
    {
        // Bỏ qua kiểm tra quyền trong môi trường test
        if (app()->environment('testing')) {
            return true;
        }

        return AuthUtils::userCan('create_books');
    }
}
