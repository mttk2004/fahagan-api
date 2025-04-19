<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;

class AddressUpdateRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        return [
          'name' => ['sometimes', 'string', 'max:255'],
          'phone' => ['sometimes', 'string', 'regex:/^0[35789][0-9]{8}$/'],
          'city' => ['sometimes', 'string', 'max:255'],
          'district' => ['sometimes', 'string', 'max:255'],
          'ward' => ['sometimes', 'string', 'max:255'],
          'address_line' => ['sometimes', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
          'name.string' => 'Tên nên là một chuỗi.',
          'name.max' => 'Tên không được vượt quá 255 ký tự.',

          'phone.string' => 'Số điện thoại nên là một chuỗi.',
          'phone.regex' => 'Số điện thoại không hợp lệ.',

          'city.string' => 'Thành phố nên là một chuỗi.',
          'city.max' => 'Thành phố không được vượt quá 255 ký tự.',

          'district.string' => 'Quận/Huyện nên là một chuỗi.',
          'district.max' => 'Quận/Huyện không được vượt quá 255 ký tự.',

          'ward.string' => 'Phường/Xã nên là một chuỗi.',
          'ward.max' => 'Phường/Xã không được vượt quá 255 ký tự.',

          'address_line.string' => 'Địa chỉ nên là một chuỗi.',
          'address_line.max' => 'Địa chỉ không được vượt quá 255 ký tự.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
