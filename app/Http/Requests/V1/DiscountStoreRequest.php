<?php

namespace App\Http\Requests\V1;

use App\Enums\Discount\DiscountValidationMessages;
use App\Enums\Discount\DiscountValidationRules;
use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Utils\AuthUtils;

class DiscountStoreRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        return [
            'data.attributes.name' => DiscountValidationRules::getNameRuleWithUnique(),
            'data.attributes.discount_type' => DiscountValidationRules::DISCOUNT_TYPE->rules(),
            'data.attributes.discount_value' => DiscountValidationRules::DISCOUNT_VALUE->rules(),
            'data.attributes.start_date' => DiscountValidationRules::START_DATE->rules(),
            'data.attributes.end_date' => DiscountValidationRules::END_DATE->rules(),

            'data.relationships.targets' => ['required', 'array'],
            'data.relationships.targets.*.type' => [
                'required',
                'string',
                'in:book,author,publisher,genre',
            ],
            'data.relationships.targets.*.id' => ['required', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'data.attributes.name.required' => DiscountValidationMessages::NAME_REQUIRED->message(),
            'data.attributes.name.string' => DiscountValidationMessages::NAME_STRING->message(),
            'data.attributes.name.max' => DiscountValidationMessages::NAME_MAX->message(),
            'data.attributes.name.unique' => DiscountValidationMessages::NAME_UNIQUE->message(),

            'data.attributes.discount_type.required' => DiscountValidationMessages::DISCOUNT_TYPE_REQUIRED->message(),
            'data.attributes.discount_type.string' => DiscountValidationMessages::DISCOUNT_TYPE_STRING->message(),
            'data.attributes.discount_type.in' => DiscountValidationMessages::DISCOUNT_TYPE_IN->message(),

            'data.attributes.discount_value.required' => DiscountValidationMessages::DISCOUNT_VALUE_REQUIRED->message(),
            'data.attributes.discount_value.numeric' => DiscountValidationMessages::DISCOUNT_VALUE_NUMERIC->message(),
            'data.attributes.discount_value.min' => DiscountValidationMessages::DISCOUNT_VALUE_MIN->message(),

            'data.attributes.start_date.required' => DiscountValidationMessages::START_DATE_REQUIRED->message(),
            'data.attributes.start_date.date' => DiscountValidationMessages::START_DATE_DATE->message(),
            'data.attributes.start_date.before_or_equal' => DiscountValidationMessages::START_DATE_BEFORE_OR_EQUAL->message(),

            'data.attributes.end_date.required' => DiscountValidationMessages::END_DATE_REQUIRED->message(),
            'data.attributes.end_date.date' => DiscountValidationMessages::END_DATE_DATE->message(),
            'data.attributes.end_date.after_or_equal' => DiscountValidationMessages::END_DATE_AFTER_OR_EQUAL->message(),

            'data.relationships.targets.required' => 'Đối tượng áp dụng là trường bắt buộc.',
            'data.relationships.targets.array' => 'Đối tượng áp dụng nên là một mảng.',
            'data.relationships.targets.*.type.required' => 'Loại đối tượng áp dụng là trường bắt buộc.',
            'data.relationships.targets.*.type.string' => 'Loại đối tượng áp dụng nên là một chuỗi.',
            'data.relationships.targets.*.type.in' => 'Loại đối tượng áp dụng không hợp lệ.',
            'data.relationships.targets.*.id.required' => 'ID đối tượng áp dụng là trường bắt buộc.',
            'data.relationships.targets.*.id.integer' => 'ID đối tượng áp dụng nên là một số.',
        ];
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('create_discounts');
    }
}
