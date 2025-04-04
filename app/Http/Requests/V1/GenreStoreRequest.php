<?php

namespace App\Http\Requests\V1;

use App\Enums\Genre\GenreValidationMessages;
use App\Enums\Genre\GenreValidationRules;
use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Traits\HasApiJsonValidation;
use App\Traits\HasRequestFormat;
use App\Utils\AuthUtils;

class GenreStoreRequest extends BaseRequest implements HasValidationMessages
{
    use HasApiJsonValidation;
    use HasRequestFormat;

    /**
     * Chuẩn bị dữ liệu trước khi validation
     */
    protected function prepareForValidation(): void
    {
        // Chuyển đổi từ direct format sang JSON:API format
        // Genre không có relationships, được phép sử dụng direct format
        $this->convertToJsonApiFormat([
            'name',
            'slug'
        ]);
    }

    public function rules(): array
    {
        $attributesRules = $this->mapAttributesRules([
            'name' => GenreValidationRules::getNameRuleWithUnique(),
            'slug' => GenreValidationRules::getSlugRuleWithUnique(),
            'description' => GenreValidationRules::DESCRIPTION->rules(),
        ]);

        return $attributesRules;
    }

    public function messages(): array
    {
        return GenreValidationMessages::getJsonApiMessages();
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('create_genres');
    }
}
