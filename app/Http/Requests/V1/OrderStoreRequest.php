<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class OrderStoreRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    return [
      'shipping_address_id' => 'required|integer|exists:addresses,id',
      'payment_method' => 'required|string|in:cod,banking,momo,zalopay',
      'note' => 'nullable|string|max:500',
      'coupon_code' => 'nullable|string|max:50',
    ];
  }

  /**
   * Get custom messages for validator errors.
   *
   * @return array
   */
  public function messages(): array
  {
    return [
      'shipping_address_id.required' => 'Vui lòng chọn địa chỉ giao hàng',
      'shipping_address_id.exists' => 'Địa chỉ giao hàng không tồn tại',
      'payment_method.required' => 'Vui lòng chọn phương thức thanh toán',
      'payment_method.in' => 'Phương thức thanh toán không hợp lệ',
    ];
  }
}
