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
            'data.attributes.discount_type' => ['required', 'string', 'in:percent,fixed'],
            'data.attributes.discount_value' => ['required', 'decimal:', 'min:5'],
            'data.attributes.start_date' => ['required', 'date', 'after:today'],
            'data.attributes.end_date' => ['required', 'date', 'after:data.attributes.start_date'],

            'data.relationships.targets' => ['required', 'array'],
            'data.relationships.targets.*.type' => [
                'required',
                'string',
                'in:book,author,publisher,genre',
            ],
            'data.relationships.targets.*.id' => ['required', 'integer'],
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
            'data.attributes.discount_value.min' => 'Giá trị giảm giá không hợp lệ.',

            'data.attributes.start_date.required' => 'Ngày bắt đầu là trường bắt buộc.',
            'data.attributes.start_date.date' => 'Ngày bắt đầu không hợp lệ.',
            'data.attributes.start_date.after' => 'Ngày bắt đầu phải sau ngày hiện tại.',

            'data.attributes.end_date.required' => 'Ngày kết thúc là trường bắt buộc.',
            'data.attributes.end_date.date' => 'Ngày kết thúc không hợp lệ.',
            'data.attributes.end_date.after' => 'Ngày kết thúc phải sau ngày bắt đầu.',

            'data.relationships.targets.required' => 'Đối tượng áp dụng là trường bắt buộc.',
            'data.relationships.targets.array' => 'Đối tượng áp dụng nên là một mảng.',
            'data.relationships.targets.*.type.required' => 'Loại đối tượng áp dụng là trường bắt buộc.',
            'data.relationships.targets.*.type.string' => 'Loại đối tượng áp dụng nên là một chuỗi.',
            'data.relationships.targets.*.type.in' => 'Loại đối tượng áp dụng không hợp lệ.',
            'data.relationships.targets.*.id.required' => 'ID đối tượng áp dụng là trường bắt buộc.',
            'data.relationships.targets.*.id.integer' => 'ID đối tượng áp dụng nên là một số.',
        ];
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('create_discounts');
    }
}
