<?php

namespace App\Http\Resources\V1;

use App\Enums\ResponseMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/** @see \App\Models\Author */
class AuthorCollection extends ResourceCollection
{
  public function toArray(Request $request): array
  {
    return [
      'status' => 200,
      'message' => ResponseMessage::LOAD_AUTHORS_SUCCESS,
      'data' => $this->collection,
    ];
  }
}
