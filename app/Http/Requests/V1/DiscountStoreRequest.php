<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Utils\AuthUtils;

class DiscountStoreRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        return [
            'data.attributes.name' => ['required', 'string', 'max:255', 'unique:discounts,name'],
            'data.attributes.discount_type' => ['required', 'string', 'in:percentage,fixed'],
            'data.attributes.discount_value' => ['required', 'decimal:0,1', 'min:0.0'],
            'data.attributes.target_type' => ['required', 'string', 'in:book,order'],
            'data.attributes.start_date' => ['required', 'date', 'after_or_equal:today'],
            'data.attributes.end_date' => ['required', 'date', 'after:data.attributes.start_date'],
            'data.attributes.min_purchase_amount' => ['nullable', 'decimal:0,1', 'min:0.0'],
            'data.attributes.max_discount_amount' => ['nullable', 'decimal:0,1', 'min:0.0'],
            'data.attributes.description' => ['nullable', 'string', 'max:1000'],
            'data.attributes.is_active' => ['boolean'],

            // Target là bắt buộc chỉ khi target_type là book
            'data.relationships.targets' => ['required_if:data.attributes.target_type,book', 'array'],
            'data.relationships.targets.*.type' => [
                'required_with:data.relationships.targets',
                'string',
                'in:book',
            ],
            'data.relationships.targets.*.id' => ['required_with:data.relationships.targets', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'data.attributes.name.required' => 'Tên mã giảm giá là trường bắt buộc.',
            'data.attributes.name.string' => 'Tên mã giảm giá nên là một chuỗi.',
            'data.attributes.name.max' => 'Tên mã giảm giá nên có độ dài tối đa 255.',
            'data.attributes.name.unique' => 'Tên mã giảm giá đã tồn tại.',

            'data.attributes.discount_type.required' => 'Loại giảm giá là trường bắt buộc.',
            'data.attributes.discount_type.string' => 'Loại giảm giá nên là một chuỗi.',
            'data.attributes.discount_type.in' => 'Loại giảm giá không hợp lệ.',

            'data.attributes.discount_value.required' => 'Giá trị giảm giá là trường bắt buộc.',
            'data.attributes.discount_value.decimal' => 'Giá trị giảm giá nên là một số.',
            'data.attributes.discount_value.min' => 'Giá trị giảm giá không được âm.',

            'data.attributes.target_type.required' => 'Loại đối tượng giảm giá là trường bắt buộc.',
            'data.attributes.target_type.string' => 'Loại đối tượng giảm giá nên là một chuỗi.',
            'data.attributes.target_type.in' => 'Loại đối tượng giảm giá phải là book hoặc order.',

            'data.attributes.min_purchase_amount.decimal' => 'Điều kiện đơn hàng tối thiểu nên là một số.',
            'data.attributes.min_purchase_amount.min' => 'Điều kiện đơn hàng tối thiểu không được âm.',

            'data.attributes.max_discount_amount.decimal' => 'Giá trị giảm giá tối đa nên là một số.',
            'data.attributes.max_discount_amount.min' => 'Giá trị giảm giá tối đa không được âm.',

            'data.attributes.start_date.required' => 'Ngày bắt đầu là trường bắt buộc.',
            'data.attributes.start_date.date' => 'Ngày bắt đầu không hợp lệ.',
            'data.attributes.start_date.after_or_equal' => 'Ngày bắt đầu phải sau hoặc bằng ngày hiện tại.',

            'data.attributes.end_date.required' => 'Ngày kết thúc là trường bắt buộc.',
            'data.attributes.end_date.date' => 'Ngày kết thúc không hợp lệ.',
            'data.attributes.end_date.after' => 'Ngày kết thúc phải sau ngày bắt đầu.',

            'data.attributes.description.string' => 'Mô tả nên là một chuỗi.',
            'data.attributes.description.max' => 'Mô tả nên có độ dài tối đa 1000 ký tự.',

            'data.attributes.is_active.boolean' => 'Trạng thái hoạt động phải là true hoặc false.',

            'data.relationships.targets.required_if' => 'Đối tượng áp dụng là trường bắt buộc khi loại đối tượng giảm giá là sách.',
            'data.relationships.targets.array' => 'Đối tượng áp dụng nên là một mảng.',
            'data.relationships.targets.*.type.required_with' => 'Loại đối tượng áp dụng là trường bắt buộc.',
            'data.relationships.targets.*.type.string' => 'Loại đối tượng áp dụng nên là một chuỗi.',
            'data.relationships.targets.*.type.in' => 'Loại đối tượng áp dụng phải là book.',
            'data.relationships.targets.*.id.required_with' => 'ID đối tượng áp dụng là trường bắt buộc.',
            'data.relationships.targets.*.id.string' => 'ID đối tượng áp dụng nên là một chuỗi.',
        ];
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('create_discounts');
    }

    public function bodyParameters(): array
    {
        return [
            'data.attributes.name' => [
                'description' => 'Tên mã giảm giá',
                'example' => 'Mã giảm giá 10%',
            ],
            'data.attributes.discount_type' => [
                'description' => 'Loại giảm giá',
                'example' => 'percentage',
            ],
            'data.attributes.discount_value' => [
                'description' => 'Giá trị giảm giá',
                'example' => '10',
            ],
            'data.attributes.target_type' => [
                'description' => 'Loại đối tượng giảm giá',
                'example' => 'book',
            ],
            'data.attributes.start_date' => [
                'description' => 'Ngày bắt đầu',
                'example' => '2021-01-01',
            ],
            'data.attributes.end_date' => [
                'description' => 'Ngày kết thúc',
                'example' => '2021-01-01',
            ],
            'data.attributes.min_purchase_amount' => [
                'description' => 'Điều kiện đơn hàng/giá sách tối thiểu',
                'example' => '100000',
            ],
            'data.attributes.max_discount_amount' => [
                'description' => 'Giá trị giảm giá tối đa',
                'example' => '10000',
            ],
            'data.attributes.description' => [
                'description' => 'Mô tả',
                'example' => 'Mã giảm giá 10% cho đơn hàng trên 100.000đ',
            ],
            'data.attributes.is_active' => [
                'description' => 'Trạng thái hoạt động',
                'example' => 'true',
            ],
            'data.relationships.targets' => [
                'description' => 'Đối tượng áp dụng (chỉ sử dụng khi target_type là book)',
                'example' => '1',
            ],
            'data.relationships.targets.*.id' => [
                'description' => 'ID đối tượng áp dụng',
                'example' => '1',
            ],
        ];
    }
}
