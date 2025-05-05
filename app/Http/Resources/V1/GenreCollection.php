<?php

namespace App\Http\Resources\V1;

use App\Enums\ResponseMessage;
use Illuminate\Http\Request;

/** @see \App\Models\Genre */
class GenreCollection extends BaseCollection
{
  public function toArray(Request $request): array
  {
    if ($this->getIsDirectResponse()) {
      return [
        'status' => 200,
        'message' => ResponseMessage::LOAD_GENRES_SUCCESS,
        'data' => $this->collection,
      ];
    }

    return [
      'data' => $this->collection,
    ];
  }
}
