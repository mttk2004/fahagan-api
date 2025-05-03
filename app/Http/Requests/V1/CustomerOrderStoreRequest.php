<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Utils\AuthUtils;

class CustomerOrderStoreRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        return [
          'data.attributes.method' => ['required', 'string', 'in:cash,bank_transfer'],

          'data.relationships.address.id' => [
            'required',
            'integer',
            'exists:addresses,id',
            function ($attribute, $value, $fail) {
                $address = \App\Models\Address::find($value);
                if (! $address || $address->user_id !== AuthUtils::user()->id) {
                    $fail('ID địa chỉ không tồn tại.');
                }
            },
          ],
          'data.relationships.items' => ['required', 'array'],
          'data.relationships.items.*.id' => [
            'required',
            'integer',
            'exists:cart_items,id',
            function ($attribute, $value, $fail) {
                $cartItem = \App\Models\CartItem::find($value);
                if (! $cartItem || $cartItem->user_id !== AuthUtils::user()->id) {
                    $fail('ID sản phẩm không tồn tại trong giỏ hàng.');
                }
            },
      ],
          'data.relationships.items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
          // Method
          // cash, bank_transfer
          // cash: Tiền mặt
          // bank_transfer: Chuyển khoản ngân hàng
          'data.attributes.method.required' => 'Phương thức thanh toán là trường bắt buộc.',
          'data.attributes.method.string' => 'Phương thức thanh toán nên là một chuỗi.',
          'data.attributes.method.in' => 'Phương thức thanh toán không hợp lệ.',

          'data.relationships.address.id.required' => 'ID địa chỉ là trường bắt buộc.',
          'data.relationships.address.id.integer' => 'ID địa chỉ nên là một số nguyên.',
          'data.relationships.address.id.exists' => 'ID địa chỉ không tồn tại.',

          'data.relationships.items.required' => 'Danh sách sản phẩm là trường bắt buộc.',
          'data.relationships.items.array' => 'Danh sách sản phẩm nên là một mảng.',
          'data.relationships.items.*.id.required' => 'ID sản phẩm là trường bắt buộc.',
          'data.relationships.items.*.id.integer' => 'ID sản phẩm nên là một số nguyên.',
          'data.relationships.items.*.id.exists' => 'ID sản phẩm không tồn tại trong giỏ hàng.',
          'data.relationships.items.*.quantity.required' => 'Số lượng là trường bắt buộc.',
          'data.relationships.items.*.quantity.integer' => 'Số lượng nên là một số nguyên.',
          'data.relationships.items.*.quantity.min' => 'Số lượng không được nhỏ hơn 1.',
        ];
    }

    public function authorize(): bool
    {
        return AuthUtils::user()->is_customer;
    }
}
