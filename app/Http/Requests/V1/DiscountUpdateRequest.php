<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Utils\AuthUtils;

class DiscountUpdateRequest extends BaseRequest implements HasValidationMessages
{
  public function rules(): array
  {
    return [
      'data.attributes.name' => ['sometimes', 'string', 'max:255', 'unique:discounts,name'],
      'data.attributes.discount_type' => ['sometimes', 'string', 'in:percentage,fixed'],
      'data.attributes.discount_value' => ['sometimes', 'decimal:', 'min:0'],
      'data.attributes.target_type' => ['sometimes', 'string', 'in:book,order'],
      'data.attributes.start_date' => ['sometimes', 'date', 'after_or_equal:today'],
      'data.attributes.end_date' => ['sometimes', 'date', 'after:data.attributes.start_date'],
      'data.attributes.description' => ['nullable', 'string', 'max:1000'],
      'data.attributes.is_active' => ['boolean'],

      'data.relationships.targets' => ['sometimes', 'array'],
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
      'data.attributes.name.string' => 'Tên mã giảm giá nên là một chuỗi.',
      'data.attributes.name.max' => 'Tên mã giảm giá nên có độ dài tối đa 255.',
      'data.attributes.name.unique' => 'Tên mã giảm giá đã tồn tại.',

      'data.attributes.discount_type.string' => 'Loại giảm giá nên là một chuỗi.',
      'data.attributes.discount_type.in' => 'Loại giảm giá không hợp lệ.',

      'data.attributes.discount_value.decimal' => 'Giá trị giảm giá nên là một số.',
      'data.attributes.discount_value.min' => 'Giá trị giảm giá không được âm.',

      'data.attributes.target_type.string' => 'Loại đối tượng giảm giá nên là một chuỗi.',
      'data.attributes.target_type.in' => 'Loại đối tượng giảm giá phải là book hoặc order.',

      'data.attributes.start_date.date' => 'Ngày bắt đầu không hợp lệ.',
      'data.attributes.start_date.after_or_equal' => 'Ngày bắt đầu phải sau hoặc bằng ngày hiện tại.',

      'data.attributes.end_date.date' => 'Ngày kết thúc không hợp lệ.',
      'data.attributes.end_date.after' => 'Ngày kết thúc phải sau ngày bắt đầu.',

      'data.attributes.description.string' => 'Mô tả nên là một chuỗi.',
      'data.attributes.description.max' => 'Mô tả nên có độ dài tối đa 1000 ký tự.',

      'data.attributes.is_active.boolean' => 'Trạng thái hoạt động phải là true hoặc false.',

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
    return AuthUtils::userCan('edit_discounts');
  }
}
