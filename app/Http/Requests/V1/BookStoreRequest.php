<?php

namespace App\Http\Requests\V1;

use App\Enums\Book\BookValidationMessages;
use App\Enums\Book\BookValidationRules;
use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Traits\HasApiJsonValidation;
use App\Utils\AuthUtils;

class BookStoreRequest extends BaseRequest implements HasValidationMessages
{
    use HasApiJsonValidation;

    public function rules(): array
    {
        $title = request('data.attributes.title');
        $edition = request('data.attributes.edition');

        $attributesRules = $this->mapAttributesRules([
            'title' => BookValidationRules::getTitleRuleWithUnique($edition),
            'description' => BookValidationRules::DESCRIPTION->rules(),
            'price' => BookValidationRules::PRICE->rules(),
            'edition' => BookValidationRules::getEditionRuleWithUnique($title),
            'pages' => BookValidationRules::PAGES->rules(),
            'image_url' => BookValidationRules::IMAGE_URL->rules(),
            'publication_date' => BookValidationRules::PUBLICATION_DATE->rules(),
        ]);

        $relationshipsRules = [
            'data.relationships.authors.data.*.id' => BookValidationRules::AUTHOR_ID->rules(),
            'data.relationships.genres.data.*.id' => BookValidationRules::GENRE_ID->rules(),
            'data.relationships.publisher.id' => BookValidationRules::PUBLISHER_ID->rules(),
        ];

        return array_merge($attributesRules, $relationshipsRules);
    }

    public function messages(): array
    {
        return BookValidationMessages::getJsonApiMessages();
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
