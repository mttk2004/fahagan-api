<?php

namespace App\Http\Requests\V1;

use App\Enums\Discount\DiscountValidationMessages;
use App\Enums\Discount\DiscountValidationRules;
use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Utils\AuthUtils;

class DiscountUpdateRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        // Trường hợp update không cần check unique với ID hiện tại, sẽ thực hiện
        // trong DiscountService
        return [
            'data.attributes.name' => array_merge(
                ['sometimes'],
                DiscountValidationRules::NAME->rules()
            ),
            'data.attributes.discount_type' => array_merge(
                ['sometimes'],
                DiscountValidationRules::DISCOUNT_TYPE->rules()
            ),
            'data.attributes.discount_value' => array_merge(
                ['sometimes'],
                DiscountValidationRules::DISCOUNT_VALUE->rules()
            ),
            'data.attributes.start_date' => array_merge(
                ['sometimes'],
                DiscountValidationRules::START_DATE->rules()
            ),
            'data.attributes.end_date' => array_merge(
                ['sometimes'],
                DiscountValidationRules::END_DATE->rules()
            ),

            'data.relationships.targets' => ['sometimes', 'array'],
            'data.relationships.targets.*.type' => [
                'sometimes',
                'string',
                'in:book,author,publisher,genre',
            ],
            'data.relationships.targets.*.id' => ['sometimes', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'data.attributes.name.string' => DiscountValidationMessages::NAME_STRING->message(),
            'data.attributes.name.max' => DiscountValidationMessages::NAME_MAX->message(),
            'data.attributes.name.unique' => DiscountValidationMessages::NAME_UNIQUE->message(),

            'data.attributes.discount_type.string' => DiscountValidationMessages::DISCOUNT_TYPE_STRING->message(),
            'data.attributes.discount_type.in' => DiscountValidationMessages::DISCOUNT_TYPE_IN->message(),

            'data.attributes.discount_value.numeric' => DiscountValidationMessages::DISCOUNT_VALUE_NUMERIC->message(),
            'data.attributes.discount_value.min' => DiscountValidationMessages::DISCOUNT_VALUE_MIN->message(),

            'data.attributes.start_date.date' => DiscountValidationMessages::START_DATE_DATE->message(),
            'data.attributes.start_date.before_or_equal' => DiscountValidationMessages::START_DATE_BEFORE_OR_EQUAL->message(),

            'data.attributes.end_date.date' => DiscountValidationMessages::END_DATE_DATE->message(),
            'data.attributes.end_date.after_or_equal' => DiscountValidationMessages::END_DATE_AFTER_OR_EQUAL->message(),

            'data.relationships.targets.array' => 'Đối tượng áp dụng nên là một mảng.',
            'data.relationships.targets.*.type.string' => 'Loại đối tượng áp dụng nên là một chuỗi.',
            'data.relationships.targets.*.type.in' => 'Loại đối tượng áp dụng không hợp lệ.',
            'data.relationships.targets.*.id.integer' => 'ID đối tượng áp dụng nên là một số.',
        ];
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('edit_discounts');
    }
}
