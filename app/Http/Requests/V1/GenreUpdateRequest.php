<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Traits\HasRequestFormat;
use App\Utils\AuthUtils;

class GenreUpdateRequest extends BaseRequest implements HasValidationMessages
{
    use HasRequestFormat;

    protected function prepareForValidation(): void
    {
        $this->convertToJsonApiFormat(['name', 'description', 'slug']);
    }

    public function rules(): array
    {
        return [
            'data.attributes.name' => ['sometimes', 'string', 'max:255', 'unique:genres,name'],
            'data.attributes.description' => ['sometimes', 'string'],
            'data.attributes.slug' => ['sometimes', 'string', 'max:255', 'unique:genres,slug'],
        ];
    }

    public function messages(): array
    {
        return [
            'data.attributes.name.string' => 'Tên thể loại nên là một chuỗi.',
            'data.attributes.name.max' => 'Tên thể loại nên có độ dài tối đa 255.',
            'data.attributes.name.unique' => 'Tên thể loại đã tồn tại.',

            'data.attributes.description.string' => 'Mô tả thể loại nên là một chuỗi.',

            'data.attributes.slug.string' => 'Slug thể loại nên là một chuỗi.',
            'data.attributes.slug.max' => 'Slug thể loại nên có độ dài tối đa 255.',
            'data.attributes.slug.unique' => 'Slug thể loại đã tồn tại.',
        ];
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('edit_genres');
    }
}
