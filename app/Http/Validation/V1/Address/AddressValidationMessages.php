<?php

namespace App\Http\Validation\V1\Address;

class AddressValidationMessages
{
    public static function getMessages(): array
    {
        return [
            'name.required' => 'Tên là trường bắt buộc.',
            'name.string' => 'Tên nên là một chuỗi.',
            'phone.required' => 'Số điện thoại là trường bắt buộc.',
            'phone.string' => 'Số điện thoại nên là một chuỗi.',
            'phone.regex' => 'Số điện thoại không hợp lệ.',
            'city.required' => 'Thành phố là trường bắt buộc.',
            'city.string' => 'Thành phố nên là một chuỗi.',
            'district.required' => 'Quận/Huyện là trường bắt buộc.',
            'district.string' => 'Quận/Huyện nên là một chuỗi.',
            'ward.required' => 'Phường/Xã là trường bắt buộc.',
            'ward.string' => 'Phường/Xã nên là một chuỗi.',
            'address_line.required' => 'Địa chỉ là trường bắt buộc.',
            'address_line.string' => 'Địa chỉ nên là một chuỗi.',
        ];
    }
}
