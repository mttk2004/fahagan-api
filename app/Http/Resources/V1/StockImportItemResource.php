<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $quantity
 * @property mixed $book
 */
class StockImportItemResource extends JsonResource
{
  public function toArray(Request $request): array
  {
    return [
      'type' => 'stock_import_item',
      'id' => $this->id,
      'attributes' => [
        'quantity' => $this->quantity,
        'unit_price' => $this->unit_price,
        'sub_total' => $this->sub_total,
      ],
      'relationships' => [
        'book' => new BookResource($this->book),
      ],
    ];
  }
}
