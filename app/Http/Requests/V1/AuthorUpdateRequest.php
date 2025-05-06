<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Utils\AuthUtils;

class AuthorUpdateRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        return [
          'name' => ['sometimes', 'string', 'max:255'],
          'biography' => ['sometimes', 'string'],
          'image_url' => ['sometimes', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
          'name.string' => 'Tên tác giả phải là chuỗi',
          'name.max' => 'Tên tác giả không được vượt quá 255 ký tự',

          'biography.string' => 'Biography phải là chuỗi',

          'image_url.string' => 'Image URL phải là chuỗi',
          'image_url.max' => 'Image URL không được vượt quá 255 ký tự',
        ];
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('edit_authors');
    }

    public function bodyParameters(): array
    {
        return [
          'name' => [
            'description' => 'Tên tác giả',
            'example' => 'John Doe',
          ],
          'biography' => [
            'description' => 'Tiểu sử tác giả',
            'example' => 'John Doe is a famous author',
          ],
          'image_url' => [
            'description' => 'Ảnh tác giả',
            'example' => 'https://example.com/image.jpg',
          ],
        ];
    }
}
