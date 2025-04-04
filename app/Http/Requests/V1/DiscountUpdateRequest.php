<?php

namespace App\Http\Requests\V1;

use App\Enums\Discount\DiscountValidationMessages;
use App\Enums\Discount\DiscountValidationRules;
use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Traits\HasApiJsonValidation;
use App\Traits\HasUpdateRules;
use App\Utils\AuthUtils;

class DiscountUpdateRequest extends BaseRequest implements HasValidationMessages
{
    use HasApiJsonValidation;
    use HasUpdateRules;

    public function rules(): array
    {
        // Lấy ID giảm giá từ route parameter
        $discountId = request()->route('discount');

        $attributesRules = $this->mapAttributesRules([
            'name' => HasUpdateRules::transformToUpdateRules(
                DiscountValidationRules::getNameRuleWithUnique($discountId)
            ),
            'discount_type' => HasUpdateRules::transformToUpdateRules(
                DiscountValidationRules::DISCOUNT_TYPE->rules()
            ),
            'discount_value' => HasUpdateRules::transformToUpdateRules(
                DiscountValidationRules::DISCOUNT_VALUE->rules()
            ),
            'start_date' => HasUpdateRules::transformToUpdateRules(
                DiscountValidationRules::START_DATE->rules()
            ),
            'end_date' => HasUpdateRules::transformToUpdateRules(
                DiscountValidationRules::END_DATE->rules()
            ),
        ]);

        $relationshipsRules = [
            'data.relationships.targets' => HasUpdateRules::transformToUpdateRules(
                DiscountValidationRules::TARGET_ARRAY->rules()
            ),
            'data.relationships.targets.*.type' => HasUpdateRules::transformToUpdateRules(
                DiscountValidationRules::TARGET_TYPE->rules()
            ),
            'data.relationships.targets.*.id' => HasUpdateRules::transformToUpdateRules(
                DiscountValidationRules::TARGET_ID->rules()
            ),
        ];

        return array_merge($attributesRules, $relationshipsRules);
    }

    public function messages(): array
    {
        return DiscountValidationMessages::getJsonApiMessages();
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('edit_discounts');
    }
}
