<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Utils\AuthUtils;

class SupplierStoreRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        return [
            'data.attributes.name' => ['required', 'string', 'unique:suppliers,name'],
            'data.attributes.phone' => [
                'required',
                'string',
                'regex:/^0[35789][0-9]{8}$/',
                'unique:suppliers,phone',
            ],
            'data.attributes.email' => ['required', 'string', 'email', 'unique:suppliers,email'],
            'data.attributes.city' => ['required', 'string'],
            'data.attributes.district' => ['required', 'string'],
            'data.attributes.ward' => ['required', 'string'],
            'data.attributes.address_line' => ['required', 'string'],

            'data.relationships.books.*.id' => ['required', 'integer', 'exists:books,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'data.attributes.name.required' => 'Tên nhà cung cấp là trường bắt buộc.',
            'data.attributes.name.string' => 'Tên nhà cung cấp nên là một chuỗi.',
            'data.attributes.name.unique' => 'Tên nhà cung cấp đã tồn tại.',

            'data.attributes.phone.required' => 'Số điện thoại là trường bắt buộc.',
            'data.attributes.phone.string' => 'Số điện thoại nên là một chuỗi.',
            'data.attributes.phone.regex' => 'Số điện thoại không hợp lệ.',
            'data.attributes.phone.unique' => 'Số điện thoại đã tồn tại.',

            'data.attributes.email.required' => 'Email là trường bắt buộc.',
            'data.attributes.email.string' => 'Email nên là một chuỗi.',
            'data.attributes.email.email' => 'Email không hợp lệ.',
            'data.attributes.email.unique' => 'Email đã tồn tại.',

            'data.attributes.city.required' => 'Thành phố là trường bắt buộc.',
            'data.attributes.city.string' => 'Thành phố nên là một chuỗi.',

            'data.attributes.district.required' => 'Quận/Huyện là trường bắt buộc.',
            'data.attributes.district.string' => 'Quận/Huyện nên là một chuỗi.',

            'data.attributes.ward.required' => 'Phường/Xã là trường bắt buộc.',
            'data.attributes.ward.string' => 'Phường/Xã nên là một chuỗi.',

            'data.attributes.address_line.required' => 'Địa chỉ là trường bắt buộc.',
            'data.attributes.address_line.string' => 'Địa chỉ nên là một chuỗi.',

            'data.relationships.books.*.id.required' => 'ID sách là trường bắt buộc.',
            'data.relationships.books.*.id.integer' => 'ID sách nên là một số nguyên.',
            'data.relationships.books.*.id.exists' => 'ID sách không tồn tại.',
        ];
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('create_suppliers');
    }

    public function bodyParameters(): array
    {
        return [
            'data.attributes.name' => [
                'description' => 'Tên nhà cung cấp',
                'example' => 'Nhà cung cấp Văn học',
            ],
            'data.attributes.phone' => [
                'description' => 'Số điện thoại',
                'example' => '0909090909',
            ],
            'data.attributes.email' => [
                'description' => 'Email',
                'example' => 'example@example.com',
            ],
            'data.attributes.city' => [
                'description' => 'Thành phố',
                'example' => 'Hà Nội',
            ],
            'data.attributes.district' => [
                'description' => 'Quận/Huyện',
                'example' => 'Quận 1',
            ],
            'data.attributes.ward' => [
                'description' => 'Phường/Xã',
                'example' => 'Phường 1',
            ],
            'data.attributes.address_line' => [
                'description' => 'Địa chỉ',
                'example' => '123 Nguyễn Văn Cừ, Quận 5, Hồ Chí Minh',
            ],
            'data.relationships.books.*.id' => [
                'description' => 'ID sách',
                'example' => '1',
            ],
        ];
    }
}
