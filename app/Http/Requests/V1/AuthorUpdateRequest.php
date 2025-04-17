<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Utils\AuthUtils;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

class AuthorUpdateRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        return [
          'data.attributes.name' => ['sometimes', 'string', 'max:255'],
          'data.attributes.biography' => ['sometimes', 'string'],
          'data.attributes.image_url' => ['sometimes', 'string', 'max:255'],
          'data.relationships.books.data' => ['sometimes', 'array'],
          'data.relationships.books.data.*.id' => ['sometimes', 'exists:books,id'],
          'data.relationships.books.data.*.type' => ['sometimes', 'in:books'],
        ];
    }

    public function messages(): array
    {
        return [
          'data.attributes.name.string' => 'Tên tác giả phải là chuỗi',
          'data.attributes.name.max' => 'Tên tác giả không được vượt quá 255 ký tự',

          'data.attributes.biography.string' => 'Biography phải là chuỗi',

          'data.attributes.image_url.string' => 'Image URL phải là chuỗi',
          'data.attributes.image_url.max' => 'Image URL không được vượt quá 255 ký tự',

          'data.relationships.books.data.array' => 'Danh sách sách phải là một mảng.',
          'data.relationships.books.data.*.id.exists' => 'Sách không tồn tại.',
          'data.relationships.books.data.*.type.in' => 'Loại relationship phải là "books".',
        ];
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('edit_authors');
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @return void
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
            'message' => 'Validation errors',
            'errors' => $validator->errors(),
      ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}
