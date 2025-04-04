<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Traits\HasRequestFormat;
use App\Utils\AuthUtils;

class PublisherStoreRequest extends BaseRequest implements HasValidationMessages
{
    use HasRequestFormat;

    protected function prepareForValidation(): void
    {
        $this->convertToJsonApiFormat(['name', 'biography']);
    }

    public function rules(): array
    {
        return [
            'data.attributes.name' => ['required', 'string', 'max:255', 'unique:publishers,name'],
            'data.attributes.biography' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'data.attributes.name.required' => 'Tên nhà xuất bản là trường bắt buộc.',
            'data.attributes.name.string' => 'Tên nhà xuất bản nên là một chuỗi.',
            'data.attributes.name.max' => 'Tên nhà xuất bản nên có độ dài tối đa 255.',
            'data.attributes.name.unique' => 'Tên nhà xuất bản đã tồn tại.',

            'data.attributes.biography.required' => 'Tiểu sử nhà xuất bản là trường bắt buộc.',
            'data.attributes.biography.string' => 'Tiểu sử nhà xuất bản nên là một chuỗi.',
        ];
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('create_publishers');
    }
}
