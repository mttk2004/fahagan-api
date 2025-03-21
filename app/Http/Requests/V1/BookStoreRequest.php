<?php

namespace App\Http\Requests\V1;


use App\Http\Requests\BaseRequest;
use Illuminate\Http\Request;


class BookStoreRequest extends BaseRequest
{
	public function rules(): array
	{
		return [
			'data.attributes.title' => [
				'required',
				'string',
				'max:255',
				'unique:books,title,NULL,id,edition,' . $this->input('data.attributes.edition'),
			],
			'data.attributes.description' => ['required', 'string'],
			'data.attributes.price' => ['required', 'numeric', 'min:200000', 'max:10000000'],
			'data.attributes.edition' => ['required', 'integer', 'min:1', 'max:30'],
			'data.attributes.pages' => ['required', 'integer', 'min:50', 'max:5000'],
			'data.attributes.publication_date' => ['required', 'date', 'before:today'],
			'data.relationships.authors.data.*.id' => ['required', 'integer', 'exists:authors,id'],
			'data.relationships.genres.data.*.id' => ['required', 'integer', 'exists:genres,id'],
			'data.relationships.publisher.id' => ['required', 'integer', 'exists:publishers,id'],
		];
	}

	public function messages(): array
	{
		return [
			'data.attributes.title' => [
				'required' => 'Tiêu đề sách là trường bắt buộc.',
				'string' => 'Tiêu đề sách nên là một chuỗi.',
				'max:255' => 'Tiêu đề sách nên có độ dài tối đa 255.',
				'unique' => 'Tiêu đề sách và Số phiên bản nên là duy nhất, hãy thử thay đổi tile hoặc edition rồi thực hiện lại.',
			],
			'data.attributes.description' => [
				'required' => 'Mô tả sách là trường bắt buộc.',
				'string' => 'Mô tả sách nên là một chuỗi.',
			],
			'data.attributes.price' => [
				'required' => 'Giá sách là trường bắt buộc.',
				'string' => 'Giá sách nên là một số thực.',
				'min:200000' => 'Giá sách nên có giá trị tối thiểu 200.000,0đ',
				'max:10000000' => 'Giá sách nên có giá trị tối đa 10.000.000,0đ',
			],
			'data.attributes.edition' => [
				'required' => 'Số phiên bản là trường bắt buộc',
				'integer' => 'Số phiên bản nên là một số nguyên',
				'min:1' => 'Số phiên bản nên có giá thi tối thiểu 1',
				'max:30' => 'Số phiên bản nên có giá trị tối đa 30',
			],
			'data.attributes.pages' => [
				'required' => 'Số trang là trường bắt buộc',
				'integer' => 'Số trang nên là một số nguyên',
				'min:50' => 'Số trang nên có giá thi tối thiểu 50',
				'max:5000' => 'Số trang nên có giá trị tối đa 5000',
			],
			'data.attributes.publication_date' => [
				'required' => 'Ngày xuất bản là trường bắt buộc',
				'date' => 'Ngày xuất bản nên là một ngày',
				'before:today' => 'Ngày xuất bản nên trước ngày hôm nay',
			],
			'data.relationships.authors.data.*.id' => [
				'required' => 'id của Tác giả là trường bắt buộc',
				'integer' => 'id của Tác giả nên là một số nguyên',
				'exists' => 'id của Tác giả không tồn tại',
			],
			'data.relationships.genres.data.*.id' => [
				'required' => 'id của Thể loại là trường bắt buộc',
				'integer' => 'id của Thể loại nên là một số nguyên',
				'exists' => 'id của Thể loại không tồn tại',
			],
			'data.relationships.publisher.id' => [
				'required' => 'id của Nhà xuất bản là trường bắt buộc',
				'integer' => 'id của Nhà xuất bản nên là một số nguyên',
				'exists' => 'id của Nhà xuất bản không tồn tại',
			],
		];
	}

	public function authorize(Request $request): bool
	{
		return $request->user()->checkPermissionTo('create_books');
	}
}
