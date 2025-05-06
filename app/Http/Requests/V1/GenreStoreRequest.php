<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Utils\AuthUtils;

class GenreStoreRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        return [
          'name' => ['required', 'string', 'max:255', 'unique:genres,name'],
          'description' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
          'name.required' => 'Tên thể loại là trường bắt buộc.',
          'name.string' => 'Tên thể loại nên là một chuỗi.',
          'name.max' => 'Tên thể loại nên có độ dài tối đa 255.',
          'name.unique' => 'Tên thể loại đã tồn tại.',

          'description.required' => 'Mô tả thể loại là trường bắt buộc.',
          'description.string' => 'Mô tả thể loại nên là một chuỗi.',
        ];
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('create_genres');
    }

    public function bodyParameters(): array
    {
        return [
          'name' => [
            'description' => 'Tên thể loại',
            'example' => 'Văn học',
          ],
          'description' => [
            'description' => 'Mô tả thể loại',
            'example' => 'Thể loại văn học',
          ],
        ];
    }
}
