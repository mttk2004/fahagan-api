<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Utils\AuthUtils;

class SupplierUpdateRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        return [
            'data.attributes.name' => ['sometimes', 'string', 'unique:suppliers,name'],
            'data.attributes.phone' => [
                'sometimes',
                'string',
                'regex:/^0[35789][0-9]{8}$/',
                'unique:suppliers,phone',
            ],
            'data.attributes.email' => ['sometimes', 'string', 'email', 'unique:suppliers,email'],
            'data.attributes.city' => ['sometimes', 'string'],
            'data.attributes.ward' => ['sometimes', 'string'],
            'data.attributes.address_line' => ['sometimes', 'string'],
            'data.relationships.books.data.*.id' => ['sometimes', 'integer', 'exists:books,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'data.attributes.name.string' => 'Tên nhà cung cấp nên là một chuỗi.',
            'data.attributes.name.unique' => 'Tên nhà cung cấp đã tồn tại.',
            'data.attributes.phone.string' => 'Số điện thoại nên là một chuỗi.',
            'data.attributes.phone.regex' => 'Số điện thoại không hợp lệ.',
            'data.attributes.phone.unique' => 'Số điện thoại đã tồn tại.',
            'data.attributes.email.string' => 'Email nên là một chuỗi.',
            'data.attributes.email.email' => 'Email không hợp lệ.',
            'data.attributes.email.unique' => 'Email đã tồn tại.',
            'data.attributes.city.string' => 'Thành phố nên là một chuỗi.',
            'data.attributes.ward.string' => 'Quận/Huyện nên là một chuỗi.',
            'data.attributes.address_line.string' => 'Địa chỉ nên là một chuỗi.',
            'data.relationships.books.data.*.id.integer' => 'ID sách nên là một số nguyên.',
            'data.relationships.books.data.*.id.exists' => 'ID sách không tồn tại.',
        ];
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('edit_suppliers');
    }
}
