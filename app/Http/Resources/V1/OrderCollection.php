<?php

namespace App\Http\Resources\V1;

use App\Enums\ResponseMessage;
use Illuminate\Http\Request;

class OrderCollection extends BaseCollection
{
  public function toArray(Request $request): array
  {
    if ($this->getIsDirectResponse()) {
      return [
        'status' => 200,
        'message' => ResponseMessage::LOAD_ORDERS_SUCCESS,
        'data' => $this->collection,
      ];
    }

    return [
      'data' => $this->collection,
    ];
  }
}
