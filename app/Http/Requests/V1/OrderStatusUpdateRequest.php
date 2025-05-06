<?php

namespace App\Http\Requests\V1;

use App\Enums\OrderStatus;
use App\Http\Requests\BaseRequest;
use App\Interfaces\HasValidationMessages;
use App\Utils\AuthUtils;

class OrderStatusUpdateRequest extends BaseRequest implements HasValidationMessages
{
    public function rules(): array
    {
        return [
          'status' => [
            'required',
            'string',
            // employees can only update the status of the order to approved, delivered, canceled
            'in:approved,delivered,canceled',
          ],
        ];
    }

    public function messages(): array
    {
        return [
          'status.required' => 'Trạng thái là trường bắt buộc.',
          'status.string' => 'Trạng thái nên là một chuỗi.',
          'status.in' => 'Trạng thái không hợp lệ. Các giá trị hợp lệ là: ' . implode(', ', array_map(fn ($status) => $status->description(), OrderStatus::cases())),
        ];
    }

    public function authorize(): bool
    {
        return AuthUtils::userCan('edit_orders');
    }

    public function bodyParameters(): array
    {
        return [
          'status' => [
            'description' => 'Trạng thái đơn hàng',
            'example' => 'approved',
          ],
        ];
    }
}
