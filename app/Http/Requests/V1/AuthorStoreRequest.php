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
          'name' => ['required', 'string', 'max:255'],
          'biography' => ['required', 'string'],
          'image_url' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
          'name.required' => 'Tên tác giả là trường bắt buộc.',
          'name.string' => 'Tên tác giả nên là một chuỗi.',
          'name.max' => 'Tên tác giả nên có độ dài tối đa 255.',

          'biography.required' => 'Tiểu sử tác giả là trường bắt buộc.',
          'biography.string' => 'Tiểu sử tác giả nên là một chuỗi.',

          'image_url.required' => 'Ảnh tác giả là trường bắt buộc.',
          'image_url.string' => 'Ảnh tác giả nên là một chuỗi.',
          'image_url.max' => 'Ảnh tác giả nên có độ dài tối đa 255.',
        ];
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('create_authors');
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
