<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Utils\AuthUtils;

class StockImportStoreRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        return [
          'data.attributes.discount_value' => [
            'sometimes',
            'decimal:0,1',
            'min:0',
          ],

          'data.relationships.supplier.id' => [
            'required',
            'exists:suppliers,id',
          ],
          'data.relationships.items.*.book_id' => [
            'required',
            'exists:books,id',
            function ($attribute, $value, $fail) {
                $book = \App\Models\Book::find($value);
                $suppliedBooks = \App\Models\Supplier::find($this->input('data.relationships.supplier.id'))->suppliedBooks;
                if (! $book || ! $suppliedBooks->contains($book)) {
                    $fail('Sách không tồn tại trong danh sách sách của nhà cung cấp');
                }
            },
          ],
          'data.relationships.items.*.quantity' => [
            'required',
            'integer',
            'min:1',
      ],
          'data.relationships.items.*.unit_price' => [
            'required',
            'decimal:0,1',
            'min:0',
      ],
        ];
    }

    public function messages(): array
    {
        return [
          'data.attributes.discount_value.decimal' => 'Giá trị giảm giá phải là một số thập phân.',
          'data.attributes.discount_value.min' => 'Giá trị giảm giá phải lớn hơn 0.',
          'data.relationships.supplier.id.required' => 'Nhà cung cấp là trường bắt buộc.',
          'data.relationships.supplier.id.exists' => 'Nhà cung cấp không tồn tại.',
          'data.relationships.items.*.id.required' => 'Sách là trường bắt buộc.',
          'data.relationships.items.*.id.exists' => 'Sách không tồn tại.',
          'data.relationships.items.*.quantity.required' => 'Số lượng là trường bắt buộc.',
          'data.relationships.items.*.quantity.integer' => 'Số lượng phải là một số nguyên.',
          'data.relationships.items.*.quantity.min' => 'Số lượng phải lớn hơn 0.',
          'data.relationships.items.*.unit_price.required' => 'Giá tiền là trường bắt buộc.',
          'data.relationships.items.*.unit_price.decimal' => 'Giá tiền phải là một số thập phân.',
          'data.relationships.items.*.unit_price.min' => 'Giá tiền phải lớn hơn 0.',
        ];
    }

    public function authorize(): bool
    {
        return  AuthUtils::userCan('create_stock_imports');
    }

    public function bodyParameters(): array
    {
        return [
          'data.attributes.discount_value' => [
            'description' => 'Giá trị giảm giá',
            'example' => '100000',
          ],
          'data.relationships.supplier.id' => [
            'description' => 'ID nhà cung cấp',
            'example' => '1',
          ],
          'data.relationships.items.*.id' => [
            'description' => 'ID sách',
            'example' => '1',
          ],
          'data.relationships.items.*.quantity' => [
            'description' => 'Số lượng',
            'example' => '1',
          ],
          'data.relationships.items.*.unit_price' => [
            'description' => 'Giá tiền',
            'example' => '100000',
          ],
        ];
    }
}
