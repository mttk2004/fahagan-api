<?php

namespace App\Http\Requests\V1;

use App\Enums\Supplier\SupplierValidationMessages;
use App\Enums\Supplier\SupplierValidationRules;
use App\Http\Requests\BaseRelationshipRequest;
use App\Utils\AuthUtils;

class SupplierStoreRequest extends BaseRelationshipRequest
{
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
        return [
            'name' => SupplierValidationRules::getNameRuleWithUnique(),
            'phone' => SupplierValidationRules::PHONE->rules(),
            'email' => SupplierValidationRules::EMAIL->rules(),
            'city' => SupplierValidationRules::CITY->rules(),
            'district' => SupplierValidationRules::DISTRICT->rules(),
            'ward' => SupplierValidationRules::WARD->rules(),
            'address_line' => SupplierValidationRules::ADDRESS_LINE->rules(),
        ];
    }

    /**
     * Lấy quy tắc cho relationships
     */
    protected function getRelationshipRules(): array
    {
        return [
            'data.relationships.books.data.*.id' => SupplierValidationRules::BOOK_ID->rules(),
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
        return AuthUtils::userCan('create_suppliers');
    }
}
