<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Traits\HasRequestFormat;
use App\Utils\AuthUtils;

class AuthorUpdateRequest extends BaseRequest implements HasValidationMessages
{
    use HasRequestFormat;

    protected function prepareForValidation(): void
    {
        $this->convertToJsonApiFormat(['name', 'biography', 'image_url']);
    }

    public function rules(): array
    {
        return [
            'data.attributes.name' => ['sometimes', 'string', 'max:255'],
            'data.attributes.biography' => ['sometimes', 'string', 'max:255'],
            'data.attributes.image_url' => ['sometimes', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'data.attributes.name.string' => 'Tên tác giả phải là chuỗi',
            'data.attributes.name.max' => 'Tên tác giả không được vượt quá 255 ký tự',

            'data.attributes.biography.string' => 'Biography phải là chuỗi',
            'data.attributes.biography.max' => 'Biography không được vượt quá 255 ký tự',

            'data.attributes.image_url.string' => 'Image URL phải là chuỗi',
            'data.attributes.image_url.max' => 'Image URL không được vượt quá 255 ký tự',
        ];
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('edit_authors');
    }
}
