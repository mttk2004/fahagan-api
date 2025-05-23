<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $name
 * @property mixed $phone
 * @property mixed $email
 * @property mixed $city
 * @property mixed $district
 * @property mixed $ward
 * @property mixed $address_line
 * @property mixed $created_at
 * @property mixed $updated_at
 * @property mixed $books
 * @property mixed $suppliedBooks
 */
class SupplierResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
          'type' => 'supplier',
          'id' => $this->id,
          'attributes' => [
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'books_count' => $this->whenLoaded('books', function () {
                return $this->books->count();
            }, 0),
            'city' => $this->city,
            'district' => $this->district,
            'ward' => $this->ward,
            'address_line' => $this->address_line,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
          ],
          'relationships' => [
            'books' => BookCollection::make($this->whenLoaded('suppliedBooks')),
      ],
        ];
    }
}
