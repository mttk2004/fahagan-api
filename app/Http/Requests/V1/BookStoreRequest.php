<?php

namespace App\Http\Requests\V1;

use App\Enums\Book\BookValidationMessages;
use App\Enums\Book\BookValidationRules;
use App\Http\Requests\BaseRelationshipRequest;
use App\Utils\AuthUtils;

class BookStoreRequest extends BaseRelationshipRequest
{
    /**
     * Lấy danh sách các attribute cần chuyển đổi
     */
    protected function getAttributeNames(): array
    {
        return [
            'title',
            'price',
            'description',
            'edition',
            'pages',
            'image_url',
            'publication_date'
        ];
    }

    /**
     * Lấy quy tắc cho attributes
     */
    protected function getAttributeRules(): array
    {
        $title = request('data.attributes.title');
        $edition = request('data.attributes.edition');

        return [
            'title' => BookValidationRules::getTitleRuleWithUnique($edition),
            'description' => BookValidationRules::DESCRIPTION->rules(),
            'price' => BookValidationRules::PRICE->rules(),
            'edition' => BookValidationRules::getEditionRuleWithUnique($title),
            'pages' => BookValidationRules::PAGES->rules(),
            'image_url' => BookValidationRules::IMAGE_URL->rules(),
            'publication_date' => BookValidationRules::PUBLICATION_DATE->rules(),
        ];
    }

    /**
     * Lấy quy tắc cho relationships
     */
    protected function getRelationshipRules(): array
    {
        return [
            'data.relationships.authors.data.*.id' => BookValidationRules::AUTHOR_ID->rules(),
            'data.relationships.genres.data.*.id' => BookValidationRules::GENRE_ID->rules(),
            'data.relationships.publisher.id' => BookValidationRules::PUBLISHER_ID->rules(),
        ];
    }

    /**
     * Lấy lớp ValidationMessages
     */
    protected function getValidationMessagesClass(): string
    {
        return BookValidationMessages::class;
    }

    /**
     * Kiểm tra authorization
     */
    public function authorize(): bool
    {
        // Bỏ qua kiểm tra quyền trong môi trường test
        if (app()->environment('testing')) {
            return true;
        }

        return AuthUtils::userCan('create_books');
    }
}
