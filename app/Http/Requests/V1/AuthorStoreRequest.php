<?php

namespace App\Http\Requests\V1;

use App\Enums\Author\AuthorValidationMessages;
use App\Enums\Author\AuthorValidationRules;
use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Traits\HasApiJsonValidation;
use App\Traits\HasRequestFormat;
use App\Utils\AuthUtils;

class AuthorStoreRequest extends BaseRequest implements HasValidationMessages
{
    use HasApiJsonValidation;
    use HasRequestFormat;

    /**
     * Chuẩn bị dữ liệu trước khi validation
     */
    protected function prepareForValidation(): void
    {
        // Chuyển đổi từ direct format sang JSON:API format
        // Author có relationships books
        $this->convertToJsonApiFormat([
            'name'
        ], true);
    }

    public function rules(): array
    {
        $attributesRules = $this->mapAttributesRules([
            'name' => AuthorValidationRules::NAME->rules(),
            'biography' => AuthorValidationRules::BIOGRAPHY->rules(),
            'image_url' => AuthorValidationRules::IMAGE_URL->rules(),
        ]);

        $relationshipsRules = [
            'data.relationships.books.data.*.id' => AuthorValidationRules::BOOK_ID->rules(),
        ];

        return array_merge($attributesRules, $relationshipsRules);
    }

    public function messages(): array
    {
        return AuthorValidationMessages::getJsonApiMessages();
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('create_authors');
    }
}
