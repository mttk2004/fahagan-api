<?php

namespace App\Http\Requests\V1;


use App\Http\Requests\BaseRequest;
use App\Utils\AuthUtils;


class DiscountUpdateRequest extends BaseRequest
{
	public function rules(): array
	{
		return [
			'data.attributes.name' => ['sometimes', 'string', 'max:255', 'unique:discounts,name'],
			'data.attributes.discount_type' => ['sometimes', 'string', 'in:percent,fixed'],
			'data.attributes.discount_value' => ['sometimes', 'decimal:', 'min:0'],
			'data.attributes.start_date' => ['sometimes', 'date', 'after:today'],
			'data.attributes.end_date' => ['sometimes', 'date', 'after:data.attributes.start_date'],

			'data.relationships.targets' => ['sometimes', 'array'],
			'data.relationships.targets.*.type' => [
				'sometimes',
				'string',
				'in:book,author,publisher,genre',
			],
			'data.relationships.targets.*.id' => ['sometimes', 'integer'],
		];
	}

	public function messages(): array
	{
		return [
			'data.attributes.name.string' => 'Tên mã giảm giá nên là một chuỗi.',
			'data.attributes.name.max' => 'Tên mã giảm giá nên có độ dài tối đa 255.',
			'data.attributes.name.unique' => 'Tên mã giảm giá đã tồn tại.',
			'data.attributes.discount_type.string' => 'Loại giảm giá nên là một chuỗi.',
			'data.attributes.discount_type.in' => 'Loại giảm giá không hợp lệ.',
			'data.attributes.discount_value.decimal' => 'Giá trị giảm giá nên là một số.',
			'data.attributes.discount_value.min' => 'Giá trị giảm giá không hợp lệ.',
			'data.attributes.start_date.date' => 'Ngày bắt đầu không hợp lệ.',
			'data.attributes.start_date.after' => 'Ngày bắt đầu phải sau ngày hiện tại.',
			'data.attributes.end_date.date' => 'Ngày kết thúc không hợp lệ.',
			'data.attributes.end_date.after' => 'Ngày kết thúc phải sau ngày bắt đầu.',
			'data.relationships.targets.array' => 'Đối tượng áp dụng nên là một mảng.',
			'data.relationships.targets.*.type.string' => 'Loại đối tượng áp dụng nên là một chuỗi.',
			'data.relationships.targets.*.type.in' => 'Loại đối tượng áp dụng không hợp lệ.',
			'data.relationships.targets.*.id.integer' => 'ID đối tượng áp dụng nên là một số.',
		];
	}

	public function authorize(): bool
	{
		return AuthUtils::userCan('edit_discounts');
	}
}
