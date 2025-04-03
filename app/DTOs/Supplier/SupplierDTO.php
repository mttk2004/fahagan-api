<?php

namespace App\DTOs\Supplier;

class SupplierDTO
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
     * @param array $requestData
     * @return self
     */
    public static function fromRequestData(array $requestData): self
    {
        return new self(
            name: $requestData['name'] ?? null,
            phone: $requestData['phone'] ?? null,
            email: $requestData['email'] ?? null,
            city: $requestData['city'] ?? null,
            district: $requestData['district'] ?? null,
            ward: $requestData['ward'] ?? null,
            address_line: $requestData['address_line'] ?? null
        );
    }
}
