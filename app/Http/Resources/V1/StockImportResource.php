<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $original_total_cost
 * @property mixed $discount_value
 * @property mixed $imported_at
 * @property mixed $created_at
 * @property mixed $updated_at
 * @property mixed $employee
 * @property mixed $supplier
 * @property mixed $items
 */
class StockImportResource extends JsonResource
{
  public function toArray(Request $request): array
  {
    return [
      'type' => 'stock_import',
      'id' => $this->id,
      'attributes' => [
        'original_total_cost' => $this->original_total_cost,
        'discount_value' => $this->discount_value,
        'imported_at' => $this->imported_at,
        'created_at' => $this->created_at,
        'updated_at' => $this->updated_at,
      ],
      'relationships' => [
        'employee' => UserResource::make($this->whenLoaded('employee')),
        'supplier' => SupplierResource::make($this->whenLoaded('supplier')),
        'items' => StockImportItemCollection::make($this->whenLoaded('items')),
      ],
    ];
  }
}
