<?php

namespace App\Http\Requests\V1;

use App\DTOs\CartItem\CartItemDTO;
use App\Enums\CartItem\CartItemValidationMessages;
use App\Enums\CartItem\CartItemValidationRules;
use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Traits\HasApiJsonValidation;
use App\Traits\HasRequestFormat;

class AddToCartRequest extends BaseRequest implements HasValidationMessages
{
    use HasApiJsonValidation;
    use HasRequestFormat;

    /**
     * Chuẩn bị dữ liệu trước khi validation
     */
    protected function prepareForValidation(): void
    {
        // Chuyển đổi từ direct format sang JSON:API format
        // AddToCart không có relationships, được phép sử dụng direct format
        $this->convertToJsonApiFormat([
            'book_id',
            'quantity'
        ]);
    }

    public function rules(): array
    {
        $attributesRules = $this->mapAttributesRules([
            'book_id' => CartItemValidationRules::BOOK_ID->rules(),
            'quantity' => CartItemValidationRules::QUANTITY->rules(),
        ]);

        return $attributesRules;
    }

    public function messages(): array
    {
        return CartItemValidationMessages::getJsonApiMessages();
    }

    public function authorize(): bool
    {
        return true;
    }

    public function toDTO(): CartItemDTO
    {
        return CartItemDTO::fromRequest($this->validated());
    }
}
