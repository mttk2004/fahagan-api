<?php

namespace App\DTOs;

class AddressDTO extends BaseDTO
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $phone = null,
        public readonly ?string $city = null,
        public readonly ?string $district = null,
        public readonly ?string $ward = null,
        public readonly ?string $address_line = null,
    ) {
    }

    public static function fromRequest(array $validatedData): self
    {
        return new self(
            name: $validatedData['name'] ?? null,
            phone: $validatedData['phone'] ?? null,
            city: $validatedData['city'] ?? null,
            district: $validatedData['district'] ?? null,
            ward: $validatedData['ward'] ?? null,
            address_line: $validatedData['address_line'] ?? null,
        );
    }

    public function toArray(): array
    {
        $data = [];

        if ($this->name !== null) {
            $data['name'] = $this->name;
        }

        if ($this->phone !== null) {
            $data['phone'] = $this->phone;
        }

        if ($this->city !== null) {
            $data['city'] = $this->city;
        }

        if ($this->district !== null) {
            $data['district'] = $this->district;
        }

        if ($this->ward !== null) {
            $data['ward'] = $this->ward;
        }

        if ($this->address_line !== null) {
            $data['address_line'] = $this->address_line;
        }

        return $data;
    }
}
