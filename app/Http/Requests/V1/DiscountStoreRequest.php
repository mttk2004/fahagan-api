<?php

namespace App\Http\Requests\V1;

use App\Enums\Discount\DiscountValidationMessages;
use App\Enums\Discount\DiscountValidationRules;
use App\Http\Requests\BaseRelationshipRequest;
use App\Utils\AuthUtils;

class DiscountStoreRequest extends BaseRelationshipRequest
{
    /**
     * Lấy danh sách các attribute cần chuyển đổi
     */
    protected function getAttributeNames(): array
    {
        return [
            'name',
            'discount_type',
            'discount_value',
            'start_date',
            'end_date',
        ];
    }

    /**
     * Lấy quy tắc cho attributes
     */
    protected function getAttributeRules(): array
    {
        return [
            'name' => DiscountValidationRules::getNameRuleWithUnique(),
            'discount_type' => DiscountValidationRules::DISCOUNT_TYPE->rules(),
            'discount_value' => DiscountValidationRules::DISCOUNT_VALUE->rules(),
            'start_date' => DiscountValidationRules::START_DATE->rules(),
            'end_date' => DiscountValidationRules::END_DATE->rules(),
        ];
    }

    /**
     * Lấy quy tắc cho relationships
     */
    protected function getRelationshipRules(): array
    {
        return [
            'data.relationships.targets' => DiscountValidationRules::TARGET_ARRAY->rules(),
            'data.relationships.targets.*.type' => DiscountValidationRules::TARGET_TYPE->rules(),
            'data.relationships.targets.*.id' => DiscountValidationRules::TARGET_ID->rules(),
        ];
    }

    /**
     * Lấy lớp ValidationMessages
     */
    protected function getValidationMessagesClass(): string
    {
        return DiscountValidationMessages::class;
    }

    /**
     * Kiểm tra authorization
     */
    public function authorize(): bool
    {
        return AuthUtils::userCan('create_discounts');
    }
}
