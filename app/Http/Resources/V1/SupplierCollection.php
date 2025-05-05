<?php

namespace App\Http\Resources\V1;

use App\Enums\ResponseMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SupplierCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
          'status' => 200,
          'message' => ResponseMessage::LOAD_SUPPLIERS_SUCCESS,
          'data' => $this->collection,
        ];
    }
}
