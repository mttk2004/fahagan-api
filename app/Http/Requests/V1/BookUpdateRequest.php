<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Utils\AuthUtils;

class BookUpdateRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        return [
            'data.attributes.title' => [
                'sometimes',
                'string',
                'max:255',
                'unique:books,title,NULL,id,edition,' . request('data.attributes.edition'),
            ],
            'data.attributes.description' => ['sometimes', 'string'],
            'data.attributes.price' => ['sometimes', 'numeric', 'min:200000', 'max:10000000'],
            'data.attributes.edition' => ['sometimes', 'integer', 'min:1', 'max:30'],
            'data.attributes.pages' => ['sometimes', 'integer', 'min:50', 'max:5000'],
            'data.attributes.image_url' => ['sometimes', 'string', 'url'],
            'data.attributes.publication_date' => ['sometimes', 'date', 'before:today'],
            'data.relationships.authors.data.*.id' => ['sometimes', 'integer', 'exists:authors,id'],
            'data.relationships.genres.data.*.id' => ['sometimes', 'integer', 'exists:genres,id'],
            'data.relationships.publisher.id' => ['sometimes', 'integer', 'exists:publishers,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'data.attributes.title.string' => 'Tiêu đề sách nên là một chuỗi.',
            'data.attributes.title.max' => 'Tiêu đề sách nên có độ dài tối đa 255.',
            'data.attributes.title.unique' => 'Tiêu đề sách và Số phiên bản nên là duy nhất, hãy thử thay đổi title hoặc edition rồi thực hiện lại.',

            'data.attributes.description.string' => 'Mô tả sách nên là một chuỗi.',

            'data.attributes.price.string' => 'Giá sách nên là một số thực.',
            'data.attributes.price.min' => 'Giá sách nên có giá trị tối thiểu 200.000,0đ',
            'data.attributes.price.max' => 'Giá sách nên có giá trị tối đa 10.000.000,0đ',

            'data.attributes.edition.integer' => 'Số phiên bản nên là một số nguyên',
            'data.attributes.edition.min' => 'Số phiên bản nên có giá thi tối thiểu 1',
            'data.attributes.edition.max' => 'Số phiên bản nên có giá trị tối đa 30',

            'data.attributes.pages.integer' => 'Số trang nên là một số nguyên',
            'data.attributes.pages.min' => 'Số trang nên có giá thi tối thiểu 50',
            'data.attributes.pages.max' => 'Số trang nên có giá trị tối đa 5000',

            'data.attributes.image_url.string' => 'URL hình ảnh nên là một chuỗi',
            'data.attributes.image_url.url' => 'URL hình ảnh nên là một URL hợp lệ',

            'data.attributes.publication_date.date' => 'Ngày xuất bản nên là một ngày',
            'data.attributes.publication_date.before' => 'Ngày xuất bản nên trước ngày hôm nay',

            'data.relationships.authors.data.*.id.integer' => 'id của Tác giả nên là một số nguyên',
            'data.relationships.authors.data.*.id.exists' => 'id của Tác giả không tồn tại',

            'data.relationships.genres.data.*.id.integer' => 'id của Thể loại nên là một số nguyên',
            'data.relationships.genres.data.*.id.exists' => 'id của Thể loại không tồn tại',

            'data.relationships.publisher.id.integer' => 'id của Nhà xuất bản nên là một số nguyên',
            'data.relationships.publisher.id.exists' => 'id của Nhà xuất bản không tồn tại',
        ];
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('edit_books');
    }
}
