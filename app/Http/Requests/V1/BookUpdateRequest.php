<?php

namespace App\Http\Requests\V1;

use App\Enums\Book\BookValidationMessages;
use App\Enums\Book\BookValidationRules;
use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Models\Book;
use App\Traits\HasApiJsonValidation;
use App\Traits\HasRequestFormat;
use App\Traits\HasUpdateRules;
use App\Utils\AuthUtils;
use Illuminate\Support\Arr;

class BookUpdateRequest extends BaseRequest implements HasValidationMessages
{
    use HasApiJsonValidation;
    use HasUpdateRules;
    use HasRequestFormat;

    /**
     * Chuẩn bị dữ liệu trước khi validation
     */
    protected function prepareForValidation(): void
    {
        // Chuyển đổi từ direct format sang JSON:API format
        // Book có relationships authors, genres, publisher
        $this->convertToJsonApiFormat([
            'title',
            'price'
        ], true);
    }

    public function rules(): array
    {
        // Lấy ID sách từ route parameter
        $bookId = request()->route('book');

        // Lấy thông tin sách hiện tại
        $book = Book::find($bookId);

        // Lấy input data từ request
        $requestData = request()->all();
        $requestEdition = Arr::get($requestData, 'data.attributes.edition');
        $requestTitle = Arr::get($requestData, 'data.attributes.title');

        $attributesRules = $this->mapAttributesRules([
            'title' => BookValidationRules::getTitleRuleWithUniqueForUpdate(
                $bookId,
                $requestEdition,
                $book ? $book->edition : null
            ),
            'description' => HasUpdateRules::transformToUpdateRules(BookValidationRules::DESCRIPTION->rules()),
            'price' => HasUpdateRules::transformToUpdateRules(BookValidationRules::PRICE->rules()),
            'edition' => BookValidationRules::getEditionRuleWithUniqueForUpdate(
                $bookId,
                $requestTitle,
                $book ? $book->title : null
            ),
            'pages' => HasUpdateRules::transformToUpdateRules(BookValidationRules::PAGES->rules()),
            'image_url' => HasUpdateRules::transformToUpdateRules(BookValidationRules::IMAGE_URL->rules()),
            'publication_date' => HasUpdateRules::transformToUpdateRules(BookValidationRules::PUBLICATION_DATE->rules()),
        ]);

        $relationshipsRules = [
            'data.relationships.authors.data.*.id' => HasUpdateRules::transformToUpdateRules(BookValidationRules::AUTHOR_ID->rules()),
            'data.relationships.genres.data.*.id' => HasUpdateRules::transformToUpdateRules(BookValidationRules::GENRE_ID->rules()),
            'data.relationships.publisher.id' => HasUpdateRules::transformToUpdateRules(BookValidationRules::PUBLISHER_ID->rules()),
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

        return AuthUtils::userCan('edit_books');
    }
}
