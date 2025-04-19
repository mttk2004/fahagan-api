<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Utils\AuthUtils;

class PublisherStoreRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        return [
          'name' => ['required', 'string', 'max:255', 'unique:publishers,name,NULL,id,deleted_at,NULL'],
          'biography' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
          'name.required' => 'Tên nhà xuất bản là trường bắt buộc.',
          'name.string' => 'Tên nhà xuất bản nên là một chuỗi.',
          'name.max' => 'Tên nhà xuất bản nên có độ dài tối đa 255.',
          'name.unique' => 'Tên nhà xuất bản đã tồn tại.',

          'biography.required' => 'Tiểu sử nhà xuất bản là trường bắt buộc.',
          'biography.string' => 'Tiểu sử nhà xuất bản nên là một chuỗi.',
        ];
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('create_publishers');
    }
}
