<?php

namespace App\Http\Resources\V1;

use App\Enums\ResponseMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/** @see \App\Models\Genre */
class GenreCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
          'status' => 200,
          'message' => ResponseMessage::LOAD_GENRES_SUCCESS,
          'data' => $this->collection,
        ];
    }
}
