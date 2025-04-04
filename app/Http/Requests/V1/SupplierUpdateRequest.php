<?php

namespace App\Http\Requests\V1;

use App\Enums\Supplier\SupplierValidationMessages;
use App\Enums\Supplier\SupplierValidationRules;
use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Traits\HasApiJsonValidation;
use App\Traits\HasRequestFormat;
use App\Traits\HasUpdateRules;
use App\Utils\AuthUtils;

class SupplierUpdateRequest extends BaseRequest implements HasValidationMessages
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
        // Supplier có relationships books
        $this->convertToJsonApiFormat([
            'name',
            'phone',
            'email'
        ], true);
    }

    public function rules(): array
    {
        // Lấy ID supplier từ route parameter
        $supplierId = request()->route('supplier');

        $attributesRules = $this->mapAttributesRules([
            'name' => HasUpdateRules::transformToUpdateRules(
                SupplierValidationRules::getNameRuleWithUnique($supplierId)
            ),
            'phone' => HasUpdateRules::transformToUpdateRules(
                SupplierValidationRules::PHONE->rules()
            ),
            'email' => HasUpdateRules::transformToUpdateRules(
                SupplierValidationRules::EMAIL->rules()
            ),
            'city' => HasUpdateRules::transformToUpdateRules(
                SupplierValidationRules::CITY->rules()
            ),
            'district' => HasUpdateRules::transformToUpdateRules(
                SupplierValidationRules::DISTRICT->rules()
            ),
            'ward' => HasUpdateRules::transformToUpdateRules(
                SupplierValidationRules::WARD->rules()
            ),
            'address_line' => HasUpdateRules::transformToUpdateRules(
                SupplierValidationRules::ADDRESS_LINE->rules()
            ),
            'books.*' => HasUpdateRules::transformToUpdateRules(
                SupplierValidationRules::BOOK_ID->rules()
            ),
        ]);

        $relationshipsRules = [
            'data.relationships.books.data.*.id' => HasUpdateRules::transformToUpdateRules(
                SupplierValidationRules::BOOK_ID->rules()
            ),
        ];

        return array_merge($attributesRules, $relationshipsRules);
    }

    public function messages(): array
    {
        return SupplierValidationMessages::getJsonApiMessages();
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('edit_suppliers');
    }
}
