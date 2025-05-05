<?php

namespace App\Http\Resources\V1;

use App\Enums\ResponseMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/** @see \App\Models\Publisher */
class PublisherCollection extends ResourceCollection
{
  public function toArray(Request $request): array
  {
    return [
      'status' => 200,
      'message' => ResponseMessage::LOAD_PUBLISHERS_SUCCESS,
      'data' => $this->collection
    ];
  }
}
