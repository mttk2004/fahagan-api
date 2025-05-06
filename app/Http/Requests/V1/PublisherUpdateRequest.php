<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Utils\AuthUtils;

class PublisherUpdateRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        return [
          'name' => ['sometimes', 'string', 'max:255', 'unique:publishers,name'],
          'biography' => ['sometimes', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
          'name.string' => 'Tên nhà xuất bản nên là một chuỗi.',
          'name.max' => 'Tên nhà xuất bản nên có độ dài tối đa 255.',
          'name.unique' => 'Tên nhà xuất bản đã tồn tại.',

          'biography.string' => 'Tiểu sử nhà xuất bản nên là một chuỗi.',
        ];
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('edit_publishers');
    }

    public function bodyParameters(): array
    {
        return [
          'name' => [
            'description' => 'Tên nhà xuất bản',
            'example' => 'Nhà xuất bản Văn học',
          ],
          'biography' => [
            'description' => 'Tiểu sử nhà xuất bản',
            'example' => 'Nhà xuất bản Văn học là một nhà xuất bản lớn và uy tín trong lĩnh vực nhà xuất bản sách.',
          ],
        ];
    }
}
