<?php

namespace App\Http\Requests\V1;

use App\Enums\Discount\DiscountValidationMessages;
use App\Enums\Discount\DiscountValidationRules;
use App\Http\Requests\BaseRelationshipRequest;
use App\Models\Discount;
use App\Traits\HasUpdateRules;
use App\Utils\AuthUtils;
use Illuminate\Support\Arr;

class DiscountUpdateRequest extends BaseRelationshipRequest
{
    use HasUpdateRules;

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
        $id = request()->route('discount');
        $discount = Discount::findOrFail($id);

        return [
            'name' => HasUpdateRules::transformToUpdateRules(
                DiscountValidationRules::getNameRuleWithUniqueExcept($discount->id)
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
        ];
    }

    /**
     * Lấy quy tắc cho relationships
     */
    protected function getRelationshipRules(): array
    {
        return [
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
        return AuthUtils::userCan('edit_discounts');
    }
}
