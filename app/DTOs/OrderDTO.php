<?php

namespace App\DTOs;

class OrderDTO extends BaseDTO
{
  public function __construct(
    public readonly ?string $method = 'cod',
    public readonly ?int $address_id = null,
  ) {}

  public static function fromRequest(array $validatedData): self
  {
    $attributes = $validatedData['data']['attributes'] ?? [];
    $relationships = $validatedData['data']['relationships'] ?? [];

    $address_id = $relationships['address']['id'] ?? null;

    return new self(
      method: $attributes['method'] ?? null,
      address_id: $address_id,
    );
  }

  public function toArray(): array
  {
    $data = [];

    if ($this->method !== null) {
      $data['method'] = $this->method;
    }

    if ($this->address_id !== null) {
      $data['address_id'] = $this->address_id;
    }

    return $data;
  }
}
