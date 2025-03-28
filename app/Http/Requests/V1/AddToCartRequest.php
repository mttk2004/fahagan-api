<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;

class AddToCartRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        return [
            'book_id' => 'required|integer|exists:books,id',
            'quantity' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'book_id.required' => 'ID sách là trường bắt buộc.',
            'book_id.integer' => 'ID sách nên là một số nguyên.',
            'book_id.exists' => 'ID sách không tồn tại.',
            'quantity.required' => 'Số lượng là trường bắt buộc.',
            'quantity.integer' => 'Số lượng nên là một số nguyên.',
            'quantity.min' => 'Số lượng nên lớn hơn hoặc bằng 1.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
