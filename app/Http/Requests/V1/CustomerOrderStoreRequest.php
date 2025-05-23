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
            'data.attributes.method' => ['required', 'string', 'in:cod,bank_transfer,vnpay,paypal'],

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
        ];
    }

    public function messages(): array
    {
        return [
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
        ];
    }

    public function authorize(): bool
    {
        return AuthUtils::user()->is_customer;
    }

    public function bodyParameters(): array
    {
        return [
            'data.attributes.method' => [
                'description' => 'Phương thức thanh toán',
                'example' => 'cod',
            ],
            'data.relationships.address.id' => [
                'description' => 'ID địa chỉ',
                'example' => '1',
            ],
            'data.relationships.items' => [
                'description' => 'Danh sách sản phẩm',
                'example' => '1',
            ],
            'data.relationships.items.*.id' => [
                'description' => 'ID sản phẩm',
                'example' => '1',
            ],
        ];
    }
}
