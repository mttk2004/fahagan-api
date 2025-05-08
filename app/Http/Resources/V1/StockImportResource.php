<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
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
            'discount_value' => $this->discount_value,
            'imported_at' => $this->imported_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
          ],
          'relationships' => [
            'employee' => new UserResource($this->employee),
            'supplier' => new SupplierResource($this->supplier),
            'items' => (new StockImportItemCollection($this->items))->isDirectResponse(false),
          ],
        ];
    }
}
