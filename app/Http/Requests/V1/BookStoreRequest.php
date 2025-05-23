<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Utils\AuthUtils;

class BookStoreRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        return [
            'data.attributes.title' => [
                'required',
                'string',
                'max:255',
                'unique:books,title,NULL,id,edition,'.request('data.attributes.edition'),
            ],
            'data.attributes.description' => ['required', 'string'],
            'data.attributes.price' => ['required', 'numeric', 'min:200000', 'max:10000000'],
            'data.attributes.edition' => ['required', 'integer', 'min:1', 'max:30'],
            'data.attributes.pages' => ['required', 'integer', 'min:50', 'max:5000'],
            'data.attributes.image_url' => ['sometimes', 'string', 'url'],
            'data.attributes.publication_date' => ['required', 'date', 'before:today'],
            'data.relationships.authors.*.id' => ['required', 'integer', 'exists:authors,id'],
            'data.relationships.genres.*.id' => ['required', 'integer', 'exists:genres,id'],
            'data.relationships.publisher.id' => ['required', 'integer', 'exists:publishers,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'data.attributes.title.required' => 'Tiêu đề sách là trường bắt buộc.',
            'data.attributes.title.string' => 'Tiêu đề sách nên là một chuỗi.',
            'data.attributes.title.max' => 'Tiêu đề sách nên có độ dài tối đa 255.',
            'data.attributes.title.unique' => 'Tiêu đề sách và Số phiên bản nên là duy nhất, hãy thử thay đổi title hoặc edition rồi thực hiện lại.',

            'data.attributes.description.required' => 'Mô tả sách là trường bắt buộc.',
            'data.attributes.description.string' => 'Mô tả sách nên là một chuỗi.',

            'data.attributes.price.required' => 'Giá sách là trường bắt buộc.',
            'data.attributes.price.string' => 'Giá sách nên là một số thực.',
            'data.attributes.price.min' => 'Giá sách nên có giá trị tối thiểu 200.000,0đ',
            'data.attributes.price.max' => 'Giá sách nên có giá trị tối đa 10.000.000,0đ',

            'data.attributes.edition.required' => 'Số phiên bản là trường bắt buộc',
            'data.attributes.edition.integer' => 'Số phiên bản nên là một số nguyên',
            'data.attributes.edition.min' => 'Số phiên bản nên có giá thi tối thiểu 1',
            'data.attributes.edition.max' => 'Số phiên bản nên có giá trị tối đa 30',

            'data.attributes.pages.required' => 'Số trang là trường bắt buộc',
            'data.attributes.pages.integer' => 'Số trang nên là một số nguyên',
            'data.attributes.pages.min' => 'Số trang nên có giá thi tối thiểu 50',
            'data.attributes.pages.max' => 'Số trang nên có giá trị tối đa 5000',

            'data.attributes.image_url.string' => 'URL hình ảnh nên là một chuỗi',
            'data.attributes.image_url.url' => 'URL hình ảnh nên là một URL hợp lệ',

            'data.attributes.publication_date.required' => 'Ngày xuất bản là trường bắt buộc',
            'data.attributes.publication_date.date' => 'Ngày xuất bản nên là một ngày',
            'data.attributes.publication_date.before' => 'Ngày xuất bản nên trước ngày hôm nay',

            'data.relationships.authors.*.id.required' => 'id của Tác giả là trường bắt buộc',
            'data.relationships.authors.*.id.integer' => 'id của Tác giả nên là một số nguyên',
            'data.relationships.authors.*.id.exists' => 'id của Tác giả không tồn tại',

            'data.relationships.genres.*.id.required' => 'id của Thể loại là trường bắt buộc',
            'data.relationships.genres.*.id.integer' => 'id của Thể loại nên là một số nguyên',
            'data.relationships.genres.*.id.exists' => 'id của Thể loại không tồn tại',

            'data.relationships.publisher.id.required' => 'id của Nhà xuất bản là trường bắt buộc',
            'data.relationships.publisher.id.integer' => 'id của Nhà xuất bản nên là một số nguyên',
            'data.relationships.publisher.id.exists' => 'id của Nhà xuất bản không tồn tại',
        ];
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('create_books');
    }

    public function bodyParameters(): array
    {
        return [
            'data.attributes.title' => [
                'description' => 'Tiêu đề sách',
                'example' => 'Sách văn học',
            ],
            'data.attributes.description' => [
                'description' => 'Mô tả sách',
                'example' => 'Sách văn học là một cuốn sách về văn học',
            ],
            'data.attributes.price' => [
                'description' => 'Giá sách',
                'example' => '100000',
            ],
            'data.attributes.edition' => [
                'description' => 'Số phiên bản',
                'example' => '1',
            ],
            'data.attributes.pages' => [
                'description' => 'Số trang',
                'example' => '100',
            ],
            'data.attributes.image_url' => [
                'description' => 'URL hình ảnh',
                'example' => 'https://example.com/image.jpg',
            ],
            'data.attributes.publication_date' => [
                'description' => 'Ngày xuất bản',
                'example' => '2021-01-01',
            ],
            'data.relationships.authors.*.id' => [
                'description' => 'id của Tác giả',
                'example' => '1',
            ],
            'data.relationships.genres.*.id' => [
                'description' => 'id của Thể loại',
                'example' => '1',
            ],
            'data.relationships.publisher.id' => [
                'description' => 'id của Nhà xuất bản',
                'example' => '1',
            ],
        ];
    }
}
