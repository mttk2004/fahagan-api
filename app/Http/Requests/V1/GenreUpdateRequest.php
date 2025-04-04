<?php

namespace App\Http\Requests\V1;

use App\Enums\Genre\GenreValidationMessages;
use App\Enums\Genre\GenreValidationRules;
use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Traits\HasApiJsonValidation;
use App\Traits\HasRequestFormat;
use App\Traits\HasUpdateRules;
use App\Utils\AuthUtils;

class GenreUpdateRequest extends BaseRequest implements HasValidationMessages
{
    use HasApiJsonValidation;
    use HasUpdateRules;
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
            'slug',
            'description'
        ]);
    }

    public function rules(): array
    {
        // Lấy ID thể loại từ route parameter
        $genreId = request()->route('genre');

        $attributesRules = $this->mapAttributesRules([
            'name' => HasUpdateRules::transformToUpdateRules(
                GenreValidationRules::getNameRuleWithUnique($genreId)
            ),
            'slug' => HasUpdateRules::transformToUpdateRules(
                GenreValidationRules::getSlugRuleWithUnique($genreId)
            ),
            'description' => HasUpdateRules::transformToUpdateRules(
                GenreValidationRules::DESCRIPTION->rules()
            ),
        ]);

        return $attributesRules;
    }

    public function messages(): array
    {
        return GenreValidationMessages::getJsonApiMessages();
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('edit_genres');
    }
}
