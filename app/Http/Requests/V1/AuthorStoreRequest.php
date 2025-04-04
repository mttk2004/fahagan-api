<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Traits\HasRequestFormat;
use App\Utils\AuthUtils;

class AuthorStoreRequest extends BaseRequest implements HasValidationMessages
{
    use HasRequestFormat;

    protected function prepareForValidation(): void
    {
        $this->convertToJsonApiFormat(['name', 'biography', 'image_url']);
    }

    public function rules(): array
    {
        return [
            'data.attributes.name' => ['required', 'string', 'max:255'],
            'data.attributes.biography' => ['required', 'string'],
            'data.attributes.image_url' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'data.attributes.name.required' => 'Tên tác giả là trường bắt buộc.',
            'data.attributes.name.string' => 'Tên tác giả nên là một chuỗi.',
            'data.attributes.name.max' => 'Tên tác giả nên có độ dài tối đa 255.',

            'data.attributes.biography.required' => 'Tiểu sử tác giả là trường bắt buộc.',
            'data.attributes.biography.string' => 'Tiểu sử tác giả nên là một chuỗi.',

            'data.attributes.image_url.required' => 'Ảnh tác giả là trường bắt buộc.',
            'data.attributes.image_url.string' => 'Ảnh tác giả nên là một chuỗi.',
            'data.attributes.image_url.max' => 'Ảnh tác giả nên có độ dài tối đa 255.',
        ];
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('create_authors');
    }
}
