<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Utils\AuthUtils;

class GenreUpdateRequest extends BaseRequest implements HasValidationMessages
{
  public function rules(): array
  {
    return [
      'name' => ['sometimes', 'string', 'max:255', 'unique:genres,name'],
      'description' => ['sometimes', 'string'],
    ];
  }

  public function messages(): array
  {
    return [
      'name.string' => 'Tên thể loại nên là một chuỗi.',
      'name.max' => 'Tên thể loại nên có độ dài tối đa 255.',
      'name.unique' => 'Tên thể loại đã tồn tại.',

      'description.string' => 'Mô tả thể loại nên là một chuỗi.',
    ];
  }

  public function authorize(): bool
  {
    return AuthUtils::userCan('edit_genres');
  }
}
