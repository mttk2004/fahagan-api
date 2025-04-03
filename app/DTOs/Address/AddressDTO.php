<?php

namespace App\DTOs\Address;

class AddressDTO
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

    public static function fromRequestData(array $requestData): self
    {
        return new self(
            name: $requestData['name'] ?? null,
            phone: $requestData['phone'] ?? null,
            city: $requestData['city'] ?? null,
            district: $requestData['district'] ?? null,
            ward: $requestData['ward'] ?? null,
            address_line: $requestData['address_line'] ?? null,
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
