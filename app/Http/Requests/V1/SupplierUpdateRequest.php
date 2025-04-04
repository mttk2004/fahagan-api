<?php

namespace App\Http\Requests\V1;

use App\Enums\Supplier\SupplierValidationMessages;
use App\Enums\Supplier\SupplierValidationRules;
use App\Http\Requests\BaseRelationshipRequest;
use App\Models\Supplier;
use App\Traits\HasUpdateRules;
use App\Utils\AuthUtils;

class SupplierUpdateRequest extends BaseRelationshipRequest
{
    use HasUpdateRules;

    /**
     * Lấy danh sách các attribute cần chuyển đổi
     */
    protected function getAttributeNames(): array
    {
        return [
            'name',
            'phone',
            'email',
            'city',
            'district',
            'ward',
            'address_line'
        ];
    }

    /**
     * Lấy quy tắc cho attributes
     */
    protected function getAttributeRules(): array
    {
        $id = request()->route('supplier');
        $supplier = Supplier::findOrFail($id);

        return [
            'name' => HasUpdateRules::transformToUpdateRules(
                SupplierValidationRules::getNameRuleWithUniqueExcept($supplier->id)
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
        ];
    }

    /**
     * Lấy quy tắc cho relationships
     */
    protected function getRelationshipRules(): array
    {
        return [
            'data.relationships.books.data.*.id' => HasUpdateRules::transformToUpdateRules(
                SupplierValidationRules::BOOK_ID->rules()
            ),
        ];
    }

    /**
     * Lấy lớp ValidationMessages
     */
    protected function getValidationMessagesClass(): string
    {
        return SupplierValidationMessages::class;
    }

    /**
     * Kiểm tra authorization
     */
    public function authorize(): bool
    {
        return AuthUtils::userCan('edit_suppliers');
    }
}
