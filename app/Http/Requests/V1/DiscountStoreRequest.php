<?php

namespace App\Http\Requests\V1;

use App\Enums\Discount\DiscountValidationMessages;
use App\Enums\Discount\DiscountValidationRules;
use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Traits\HasApiJsonValidation;
use App\Traits\HasRequestFormat;
use App\Utils\AuthUtils;

class DiscountStoreRequest extends BaseRequest implements HasValidationMessages
{
    use HasApiJsonValidation;
    use HasRequestFormat;

    /**
     * Chuẩn bị dữ liệu trước khi validation
     */
    protected function prepareForValidation(): void
    {
        // Chuyển đổi từ direct format sang JSON:API format
        // Discount có relationships targets
        $this->convertToJsonApiFormat([
            'name',
            'discount_type'
        ], true);
    }

    public function rules(): array
    {
        $attributeRules = $this->mapAttributesRules([
            'name' => DiscountValidationRules::getNameRuleWithUnique(),
            'discount_type' => DiscountValidationRules::DISCOUNT_TYPE->rules(),
            'discount_value' => DiscountValidationRules::DISCOUNT_VALUE->rules(),
            'start_date' => DiscountValidationRules::START_DATE->rules(),
            'end_date' => DiscountValidationRules::END_DATE->rules(),
        ]);

        $relationshipsRules = [
            'data.relationships.targets' => DiscountValidationRules::TARGET_ARRAY->rules(),
            'data.relationships.targets.*.type' => DiscountValidationRules::TARGET_TYPE->rules(),
            'data.relationships.targets.*.id' => DiscountValidationRules::TARGET_ID->rules(),
        ];

        return array_merge($attributeRules, $relationshipsRules);
    }

    public function messages(): array
    {
        $messages = DiscountValidationMessages::getJsonApiMessages();

        // Thêm thông báo cho relationships nếu cần
        return $messages;
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('create_discounts');
    }
}
