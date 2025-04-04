<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Traits\HasRequestFormat;

class AddressUpdateRequest extends BaseRequest implements HasValidationMessages
{
    use HasRequestFormat;

    protected function prepareForValidation(): void
    {
        $this->convertToJsonApiFormat([
            'name',
            'phone',
            'city',
            'district',
            'ward',
            'address_line',
        ]);
    }

    public function rules(): array
    {
        return [
            'data.attributes.name' => ['sometimes', 'string', 'max:255'],
            'data.attributes.phone' => ['sometimes', 'string', 'regex:/^0[35789][0-9]{8}$/'],
            'data.attributes.city' => ['sometimes', 'string', 'max:255'],
            'data.attributes.district' => ['sometimes', 'string', 'max:255'],
            'data.attributes.ward' => ['sometimes', 'string', 'max:255'],
            'data.attributes.address_line' => ['sometimes', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'data.attributes.name.string' => 'Tên nên là một chuỗi.',
            'data.attributes.name.max' => 'Tên không được vượt quá 255 ký tự.',

            'data.attributes.phone.string' => 'Số điện thoại nên là một chuỗi.',
            'data.attributes.phone.regex' => 'Số điện thoại không hợp lệ.',

            'data.attributes.city.string' => 'Thành phố nên là một chuỗi.',
            'data.attributes.city.max' => 'Thành phố không được vượt quá 255 ký tự.',

            'data.attributes.district.string' => 'Quận/Huyện nên là một chuỗi.',
            'data.attributes.district.max' => 'Quận/Huyện không được vượt quá 255 ký tự.',

            'data.attributes.ward.string' => 'Phường/Xã nên là một chuỗi.',
            'data.attributes.ward.max' => 'Phường/Xã không được vượt quá 255 ký tự.',

            'data.attributes.address_line.string' => 'Địa chỉ nên là một chuỗi.',
            'data.attributes.address_line.max' => 'Địa chỉ không được vượt quá 255 ký tự.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
