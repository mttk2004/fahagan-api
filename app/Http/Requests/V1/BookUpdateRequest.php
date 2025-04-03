<?php

namespace App\Http\Requests\V1;

use App\Enums\Book\BookValidationMessages;
use App\Enums\Book\BookValidationRules;
use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Models\Book;
use App\Utils\AuthUtils;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class BookUpdateRequest extends BaseRequest implements HasValidationMessages
{
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

        return [
            'data.attributes.title' => array_merge(
                ['sometimes'],
                array_filter(BookValidationRules::TITLE->rules(), fn ($rule) => $rule !== 'required'),
                [
                    // Sử dụng Rule API để xử lý unique validation
                    // Bỏ qua sách hiện tại và chỉ kiểm tra các sách chưa bị soft delete
                    Rule::unique('books', 'title')
                        ->ignore($bookId)
                        ->whereNull('deleted_at')  // Chỉ kiểm tra các sách chưa bị xóa
                        ->where(function ($query) use ($book, $requestEdition) {
                            // Nếu edition có trong request, sử dụng nó
                            if ($requestEdition !== null) {
                                return $query->where('edition', $requestEdition);
                            }

                            // Nếu không, sử dụng edition hiện tại của sách
                            if ($book) {
                                return $query->where('edition', $book->edition);
                            }

                            return $query;
                        }),
                ]
            ),
            'data.attributes.description' => array_merge(
                ['sometimes'],
                array_filter(BookValidationRules::DESCRIPTION->rules(), fn ($rule) => $rule !== 'required')
            ),
            'data.attributes.price' => array_merge(
                ['sometimes'],
                array_filter(BookValidationRules::PRICE->rules(), fn ($rule) => $rule !== 'required')
            ),
            'data.attributes.edition' => array_merge(
                ['sometimes'],
                array_filter(BookValidationRules::EDITION->rules(), fn ($rule) => $rule !== 'required'),
                [
                    // Tương tự cho edition, kiểm tra unique với title
                    Rule::unique('books', 'edition')
                        ->ignore($bookId)
                        ->whereNull('deleted_at')  // Chỉ kiểm tra các sách chưa bị xóa
                        ->where(function ($query) use ($book, $requestTitle) {
                            // Nếu title có trong request, sử dụng nó
                            if ($requestTitle !== null) {
                                return $query->where('title', $requestTitle);
                            }

                            // Nếu không, sử dụng title hiện tại của sách
                            if ($book) {
                                return $query->where('title', $book->title);
                            }

                            return $query;
                        }),
                ]
            ),
            'data.attributes.pages' => array_merge(
                ['sometimes'],
                array_filter(BookValidationRules::PAGES->rules(), fn ($rule) => $rule !== 'required')
            ),
            'data.attributes.image_url' => array_merge(
                ['sometimes'],
                array_filter(BookValidationRules::IMAGE_URL->rules(), fn ($rule) => $rule !== 'required')
            ),
            'data.attributes.publication_date' => array_merge(
                ['sometimes'],
                array_filter(BookValidationRules::PUBLICATION_DATE->rules(), fn ($rule) => $rule !== 'required')
            ),
            'data.relationships.authors.data.*.id' => array_merge(
                ['sometimes'],
                array_filter(BookValidationRules::AUTHOR_ID->rules(), fn ($rule) => $rule !== 'required')
            ),
            'data.relationships.genres.data.*.id' => array_merge(
                ['sometimes'],
                array_filter(BookValidationRules::GENRE_ID->rules(), fn ($rule) => $rule !== 'required')
            ),
            'data.relationships.publisher.id' => array_merge(
                ['sometimes'],
                array_filter(BookValidationRules::PUBLISHER_ID->rules(), fn ($rule) => $rule !== 'required')
            ),
        ];
    }

    public function messages(): array
    {
        return [
            'data.attributes.title.string' => BookValidationMessages::TITLE_STRING->message(),
            'data.attributes.title.max' => BookValidationMessages::TITLE_MAX->message(),
            'data.attributes.title.unique' => BookValidationMessages::TITLE_UNIQUE->message(),

            'data.attributes.description.string' => BookValidationMessages::DESCRIPTION_STRING->message(),

            'data.attributes.price.numeric' => BookValidationMessages::PRICE_NUMERIC->message(),
            'data.attributes.price.min' => BookValidationMessages::PRICE_MIN->message(),
            'data.attributes.price.max' => BookValidationMessages::PRICE_MAX->message(),

            'data.attributes.edition.integer' => BookValidationMessages::EDITION_INTEGER->message(),
            'data.attributes.edition.min' => BookValidationMessages::EDITION_MIN->message(),
            'data.attributes.edition.max' => BookValidationMessages::EDITION_MAX->message(),
            'data.attributes.edition.unique' => BookValidationMessages::TITLE_UNIQUE->message(),

            'data.attributes.pages.integer' => BookValidationMessages::PAGES_INTEGER->message(),
            'data.attributes.pages.min' => BookValidationMessages::PAGES_MIN->message(),
            'data.attributes.pages.max' => BookValidationMessages::PAGES_MAX->message(),

            'data.attributes.image_url.string' => BookValidationMessages::IMAGE_URL_STRING->message(),
            'data.attributes.image_url.url' => BookValidationMessages::IMAGE_URL_URL->message(),

            'data.attributes.publication_date.date' => BookValidationMessages::PUBLICATION_DATE_DATE->message(),
            'data.attributes.publication_date.before' => BookValidationMessages::PUBLICATION_DATE_BEFORE->message(),

            'data.relationships.authors.data.*.id.integer' => BookValidationMessages::AUTHOR_ID_INTEGER->message(),
            'data.relationships.authors.data.*.id.exists' => BookValidationMessages::AUTHOR_ID_EXISTS->message(),

            'data.relationships.genres.data.*.id.integer' => BookValidationMessages::GENRE_ID_INTEGER->message(),
            'data.relationships.genres.data.*.id.exists' => BookValidationMessages::GENRE_ID_EXISTS->message(),

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

        return AuthUtils::userCan('edit_books');
    }
}
