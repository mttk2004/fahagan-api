<?php

namespace App\Http\Requests\V1;

use App\Enums\Publisher\PublisherValidationMessages;
use App\Enums\Publisher\PublisherValidationRules;
use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Traits\HasApiJsonValidation;
use App\Traits\HasRequestFormat;
use App\Utils\AuthUtils;

class PublisherStoreRequest extends BaseRequest implements HasValidationMessages
{
    use HasApiJsonValidation;
    use HasRequestFormat;

    /**
     * Chuẩn bị dữ liệu trước khi validation
     */
    protected function prepareForValidation(): void
    {
        // Chuyển đổi từ direct format sang JSON:API format
        // Publisher không có relationships, được phép sử dụng direct format
        $this->convertToJsonApiFormat([
            'name'
        ]);
    }

    public function rules(): array
    {
        $attributesRules = $this->mapAttributesRules([
            'name' => PublisherValidationRules::getNameRuleWithUnique(),
            'biography' => PublisherValidationRules::BIOGRAPHY->rules(),
        ]);

        return $attributesRules;
    }

    public function messages(): array
    {
        return PublisherValidationMessages::getJsonApiMessages();
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('create_publishers');
    }
}
