<?php

namespace App\Http\Requests\V1;

use App\Enums\Book\BookValidationMessages;
use App\Enums\Book\BookValidationRules;
use App\Http\Requests\BaseRelationshipRequest;
use App\Models\Book;
use App\Traits\HasUpdateRules;
use App\Utils\AuthUtils;
use Illuminate\Support\Arr;

class BookUpdateRequest extends BaseRelationshipRequest
{
    use HasUpdateRules;

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
        $book = Book::findOrFail(request()->route('book'));

        return [
            'title' => HasUpdateRules::transformToUpdateRules(
                BookValidationRules::getTitleRuleWithUniqueExcept($book->edition, $book->id)
            ),
            'description' => HasUpdateRules::transformToUpdateRules(BookValidationRules::DESCRIPTION->rules()),
            'price' => HasUpdateRules::transformToUpdateRules(BookValidationRules::PRICE->rules()),
            'edition' => HasUpdateRules::transformToUpdateRules(
                BookValidationRules::getEditionRuleWithUniqueExcept($book->title, $book->id)
            ),
            'pages' => HasUpdateRules::transformToUpdateRules(BookValidationRules::PAGES->rules()),
            'image_url' => HasUpdateRules::transformToUpdateRules(BookValidationRules::IMAGE_URL->rules()),
            'publication_date' => HasUpdateRules::transformToUpdateRules(BookValidationRules::PUBLICATION_DATE->rules()),
        ];
    }

    /**
     * Lấy quy tắc cho relationships
     */
    protected function getRelationshipRules(): array
    {
        return [
            'data.relationships.authors.data.*.id' => HasUpdateRules::transformToUpdateRules(
                BookValidationRules::AUTHOR_ID->rules()
            ),
            'data.relationships.genres.data.*.id' => HasUpdateRules::transformToUpdateRules(
                BookValidationRules::GENRE_ID->rules()
            ),
            'data.relationships.publisher.id' => HasUpdateRules::transformToUpdateRules(
                BookValidationRules::PUBLISHER_ID->rules()
            ),
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

        return AuthUtils::userCan('edit_books');
    }
}
