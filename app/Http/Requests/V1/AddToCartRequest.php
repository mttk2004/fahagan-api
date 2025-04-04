<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Traits\HasRequestFormat;

class AddToCartRequest extends BaseRequest implements HasValidationMessages
{
    use HasRequestFormat;

    protected function prepareForValidation(): void
    {
        $this->convertToJsonApiFormat([
            'book_id',
            'quantity'
        ]);
    }

    public function rules(): array
    {
        return [
            'data.attributes.book_id' => ['required', 'exists:books,id'],
            'data.attributes.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'data.attributes.book_id.required' => 'ID sách là trường bắt buộc.',
            'data.attributes.book_id.integer' => 'ID sách nên là một số nguyên.',
            'data.attributes.book_id.exists' => 'ID sách không tồn tại.',

            'data.attributes.quantity.required' => 'Số lượng là trường bắt buộc.',
            'data.attributes.quantity.integer' => 'Số lượng nên là một số nguyên.',
            'data.attributes.quantity.min' => 'Số lượng nên lớn hơn hoặc bằng 1.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
