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
          'data.attributes.shopping_name' => ['required', 'string', 'max:255'],
          'data.attributes.shopping_phone' => ['required', 'string', 'regex:/^0[35789][0-9]{8}$/'],
          'data.attributes.shopping_city' => ['required', 'string', 'max:255'],
          'data.attributes.shopping_district' => ['required', 'string', 'max:255'],
          'data.attributes.shopping_ward' => ['required', 'string', 'max:255'],
          'data.attributes.shopping_address_line' => ['required', 'string', 'max:255'],
          'data.attributes.method' => ['required', 'string', 'in:cash,bank_transfer'],

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
          'data.attributes.shopping_name.required' => 'Tên là trường bắt buộc.',
          'data.attributes.shopping_name.string' => 'Tên nên là một chuỗi.',
          'data.attributes.shopping_name.max' => 'Tên không được vượt quá 255 ký tự.',

          'data.attributes.shopping_phone.required' => 'Số điện thoại là trường bắt buộc.',
          'data.attributes.shopping_phone.string' => 'Số điện thoại nên là một chuỗi.',
          'data.attributes.shopping_phone.regex' => 'Số điện thoại không hợp lệ.',

          'data.attributes.shopping_city.required' => 'Thành phố là trường bắt buộc.',
          'data.attributes.shopping_city.string' => 'Thành phố nên là một chuỗi.',
          'data.attributes.shopping_city.max' => 'Thành phố không được vượt quá 255 ký tự.',

          'data.attributes.shopping_district.required' => 'Quận/Huyện là trường bắt buộc.',
          'data.attributes.shopping_district.string' => 'Quận/Huyện nên là một chuỗi.',
          'data.attributes.shopping_district.max' => 'Quận/Huyện không được vượt quá 255 ký tự.',

          'data.attributes.shopping_ward.required' => 'Phường/Xã là trường bắt buộc.',
          'data.attributes.shopping_ward.string' => 'Phường/Xã nên là một chuỗi.',
          'data.attributes.shopping_ward.max' => 'Phường/Xã không được vượt quá 255 ký tự.',

          'data.attributes.shopping_address_line.required' => 'Địa chỉ là trường bắt buộc.',
          'data.attributes.shopping_address_line.string' => 'Địa chỉ nên là một chuỗi.',
          'data.attributes.shopping_address_line.max' => 'Địa chỉ không được vượt quá 255 ký tự.',

          // Method
          // cash, bank_transfer
          // cash: Tiền mặt
          // bank_transfer: Chuyển khoản ngân hàng
          'data.attributes.method.required' => 'Phương thức thanh toán là trường bắt buộc.',
          'data.attributes.method.string' => 'Phương thức thanh toán nên là một chuỗi.',
          'data.attributes.method.in' => 'Phương thức thanh toán không hợp lệ.',

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
