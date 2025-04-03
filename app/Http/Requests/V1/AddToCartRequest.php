<?php

namespace App\Http\Requests\V1;

use App\DTOs\CartItem\CartItemDTO;
use App\Enums\CartItem\CartItemValidationMessages;
use App\Enums\CartItem\CartItemValidationRules;
use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;

class AddToCartRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        return [
            'book_id' => CartItemValidationRules::BOOK_ID->rules(),
            'quantity' => CartItemValidationRules::QUANTITY->rules(),
        ];
    }

    public function messages(): array
    {
        return [
            'book_id.required' => CartItemValidationMessages::BOOK_ID_REQUIRED->message(),
            'book_id.integer' => CartItemValidationMessages::BOOK_ID_INTEGER->message(),
            'book_id.exists' => CartItemValidationMessages::BOOK_ID_EXISTS->message(),
            'quantity.required' => CartItemValidationMessages::QUANTITY_REQUIRED->message(),
            'quantity.integer' => CartItemValidationMessages::QUANTITY_INTEGER->message(),
            'quantity.min' => CartItemValidationMessages::QUANTITY_MIN->message(),
        ];
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
