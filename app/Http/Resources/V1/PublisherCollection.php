<?php

namespace App\Http\Resources\V1;

use App\Enums\ResponseMessage;
use Illuminate\Http\Request;

/** @see \App\Models\Publisher */
class PublisherCollection extends BaseCollection
{
    public function toArray(Request $request): array
    {
        if ($this->getIsDirectResponse()) {
            return [
              'status' => 200,
              'message' => ResponseMessage::LOAD_PUBLISHERS_SUCCESS,
              'data' => $this->collection,
            ];
        }

        return [
          'data' => $this->collection,
        ];
    }
}
