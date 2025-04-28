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
        public readonly ?string $address_line = null,
        public readonly array $book_ids = []
    ) {
    }

    public static function fromRequest(array $validatedData): self
    {
        $attributes = $validatedData['data']['attributes'] ?? [];
        $relationships = $validatedData['data']['relationships'] ?? [];

        // Lấy book_ids từ relationships nếu có
        $book_ids = [];
        if (isset($relationships['books']['data'])) {
            $book_ids = collect($relationships['books']['data'])->pluck('id')->toArray();
        }

        return new self(
            name: $attributes['name'] ?? null,
            phone: $attributes['phone'] ?? null,
            email: $attributes['email'] ?? null,
            city: $attributes['city'] ?? null,
            district: $attributes['district'] ?? null,
            ward: $attributes['ward'] ?? null,
            address_line: $attributes['address_line'] ?? null,
            book_ids: $book_ids
        );
    }

    public function toArray(): array
    {
        $data = [];

        if ($this->name) {
            $data['name'] = $this->name;
        }

        if ($this->phone) {
            $data['phone'] = $this->phone;
        }

        if ($this->email) {
            $data['email'] = $this->email;
        }

        if ($this->city) {
            $data['city'] = $this->city;
        }

        if ($this->district) {
            $data['district'] = $this->district;
        }

        if ($this->ward) {
            $data['ward'] = $this->ward;
        }

        if ($this->address_line) {
            $data['address_line'] = $this->address_line;
        }

        if ($this->book_ids) {
            $data['book_ids'] = $this->book_ids;
        }

        return $data;
    }
}
