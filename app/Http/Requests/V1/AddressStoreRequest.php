<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;

class AddressStoreRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^0[35789][0-9]{8}$/'],
            'city' => ['required', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:255'],
            'ward' => ['required', 'string', 'max:255'],
            'address_line' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tên là trường bắt buộc.',
            'name.string' => 'Tên nên là một chuỗi.',
            'name.max' => 'Tên không được vượt quá 255 ký tự.',

            'phone.required' => 'Số điện thoại là trường bắt buộc.',
            'phone.string' => 'Số điện thoại nên là một chuỗi.',
            'phone.regex' => 'Số điện thoại không hợp lệ.',

            'city.required' => 'Thành phố là trường bắt buộc.',
            'city.string' => 'Thành phố nên là một chuỗi.',
            'city.max' => 'Thành phố không được vượt quá 255 ký tự.',

            'district.required' => 'Quận/Huyện là trường bắt buộc.',
            'district.string' => 'Quận/Huyện nên là một chuỗi.',
            'district.max' => 'Quận/Huyện không được vượt quá 255 ký tự.',

            'ward.required' => 'Phường/Xã là trường bắt buộc.',
            'ward.string' => 'Phường/Xã nên là một chuỗi.',
            'ward.max' => 'Phường/Xã không được vượt quá 255 ký tự.',

            'address_line.required' => 'Địa chỉ là trường bắt buộc.',
            'address_line.string' => 'Địa chỉ nên là một chuỗi.',
            'address_line.max' => 'Địa chỉ không được vượt quá 255 ký tự.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'Tên của người nhận',
                'example' => 'John Doe',
            ],
            'phone' => [
                'description' => 'Số điện thoại của người nhận',
                'example' => '0909090909',
            ],
            'city' => [
                'description' => 'Thành phố của người nhận',
                'example' => 'Hà Nội',
            ],
            'district' => [
                'description' => 'Quận/Huyện của người nhận',
                'example' => 'Quận 1',
            ],
            'ward' => [
                'description' => 'Phường/Xã của người nhận',
                'example' => 'Phường 1',
            ],
            'address_line' => [
                'description' => 'Địa chỉ cụ thể của người nhận',
                'example' => '123 Nguyễn Văn Cừ, Quận 5, Hồ Chí Minh',
            ],
        ];
    }
}
