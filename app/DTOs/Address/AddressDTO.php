<?php

namespace App\DTOs\Address;

class AddressDTO extends \App\DTOs\BaseDTO
{
  public function __construct(
    public readonly ?string $name = null,
    public readonly ?string $phone = null,
    public readonly ?string $city = null,
    public readonly ?string $district = null,
    public readonly ?string $ward = null,
    public readonly ?string $address_line = null,
  ) {}

  public static function fromRequest(array $validatedData): self
  {
    $attributes = $validatedData['data']['attributes'] ?? [];

    return new self(
      name: $attributes['name'] ?? null,
      phone: $attributes['phone'] ?? null,
      city: $attributes['city'] ?? null,
      district: $attributes['district'] ?? null,
      ward: $attributes['ward'] ?? null,
      address_line: $attributes['address_line'] ?? null,
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
