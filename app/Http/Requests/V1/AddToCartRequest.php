<?php

namespace App\Http\Requests\V1;

use App\DTOs\CartItem\CartItemDTO;
use App\Enums\CartItem\CartItemValidationMessages;
use App\Enums\CartItem\CartItemValidationRules;
use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Traits\HasApiJsonValidation;
use Illuminate\Support\Arr;

class AddToCartRequest extends BaseRequest implements HasValidationMessages
{
    use HasApiJsonValidation;

    /**
     * Chuẩn bị dữ liệu trước khi validation
     */
    protected function prepareForValidation()
    {
        // Nếu người dùng gửi dữ liệu không theo format JSON:API, chuyển đổi sang format JSON:API
        if (!$this->has('data') && $this->has('book_id') && $this->has('quantity')) {
            $this->merge([
                'data' => [
                    'attributes' => [
                        'book_id' => $this->input('book_id'),
                        'quantity' => $this->input('quantity'),
                    ]
                ]
            ]);
        }
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
