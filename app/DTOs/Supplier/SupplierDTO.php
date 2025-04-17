<?php

namespace App\DTOs\Supplier;

class SupplierDTO extends \App\DTOs\BaseDTO
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $phone = null,
        public readonly ?string $email = null,
        public readonly ?string $city = null,
        public readonly ?string $district = null,
        public readonly ?string $ward = null,
        public readonly ?string $address_line = null
    ) {
    }

    public function toArray(): array
    {
        $array = [];

        if (isset($this->name)) {
            $array['name'] = $this->name;
        }

        if (isset($this->phone)) {
            $array['phone'] = $this->phone;
        }

        if (isset($this->email)) {
            $array['email'] = $this->email;
        }

        if (isset($this->city)) {
            $array['city'] = $this->city;
        }

        if (isset($this->district)) {
            $array['district'] = $this->district;
        }

        if (isset($this->ward)) {
            $array['ward'] = $this->ward;
        }

        if (isset($this->address_line)) {
            $array['address_line'] = $this->address_line;
        }

        return $array;
    }

    /**
     * Tạo DTO từ request data
     *
     * @param array $validatedData
     * @return self
     */
    public static function fromRequest(array $validatedData): self
    {
        $attributes = $validatedData['data']['attributes'] ?? [];

        return new self(
            name: $attributes['name'] ?? null,
            phone: $attributes['phone'] ?? null,
            email: $attributes['email'] ?? null,
            city: $attributes['city'] ?? null,
            district: $attributes['district'] ?? null,
            ward: $attributes['ward'] ?? null,
            address_line: $attributes['address_line'] ?? null
        );
    }
}
