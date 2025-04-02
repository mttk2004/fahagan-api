<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Utils\AuthUtils;

class AuthorStoreRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        return [
            'data.attributes.name' => [
                'required',
                'string',
                'max:255',
            ],
            'data.attributes.biography' => ['required', 'string'],
            'data.attributes.image_url' => ['required', 'string', 'url'],
        ];
    }

    public function messages(): array
    {
        return [
            'data.attributes.name' => [
                'required' => 'Tên tác giả là trường bắt buộc.',
                'string' => 'Tên tác giả nên là một chuỗi.',
                'max:255' => 'Tên tác giả nên có độ dài tối đa 255.',
            ],
            'data.attributes.biography' => [
                'required' => 'Tiểu sử tác giả là trường bắt buộc.',
                'string' => 'Tiểu sử tác giả nên là một chuỗi.',
            ],
            'data.attributes.image_url' => [
                'required' => 'Ảnh tác giả là trường bắt buộc.',
                'string' => 'Ảnh tác giả nên là một chuỗi.',
                'url' => 'Ảnh tác giả nên là một URL hợp lệ.',
            ],
        ];
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('create_authors');
    }
}
