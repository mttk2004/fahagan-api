<?php

namespace App\Http\Resources\V1;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Payment
 * @property mixed $id
 * @property mixed $status
 * @property mixed $method
 * @property mixed $total_amount
 * @property mixed $discount_value
 * @property mixed $transaction_ref
 * @property mixed $gateway_response
 * @property mixed $created_at
 * @property mixed $updated_at
 */
class PaymentResource extends JsonResource
{
  public function toArray(Request $request): array
  {
    return [
      'type' => 'payment',
      'id' => $this->id,
      'attributes' => [
        'status' => $this->status,
        'method' => $this->method,
        'total_amount' => $this->total_amount,
        'discount_value' => $this->discount_value,
        'transaction_ref' => $this->transaction_ref,
        'gateway_response' => $this->gateway_response,
        'created_at' => $this->created_at,
        'updated_at' => $this->updated_at,
      ],
    ];
  }
}
