<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $city
 * @property mixed $phone
 * @property mixed $name
 * @property mixed $address_line
 * @property mixed $ward
 */
class AddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'type' => 'address',
            'id' => $this->id,
            'attributes' => [
                'name' => $this->name,
                'phone' => $this->phone,
                'city' => $this->city,
                'district' => $this->district,
                'ward' => $this->ward,
                'address_line' => $this->address_line,
            ],
        ];
    }
}
