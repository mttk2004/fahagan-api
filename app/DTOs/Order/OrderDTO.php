<?php

namespace App\DTOs\Order;

class OrderDTO extends \App\DTOs\BaseDTO
{
  public function __construct(
    public readonly ?string $shopping_name,
    public readonly ?string $shopping_phone,
    public readonly ?string $shopping_city,
    public readonly ?string $shopping_district,
    public readonly ?string $shopping_ward,
    public readonly ?string $shopping_address_line,
    public readonly ?string $method,
    public readonly array $items = [],
  ) {}

  public static function fromRequest(array $validatedData): self
  {
    $attributes = $validatedData['data']['attributes'] ?? [];
    $relationships = $validatedData['data']['relationships'] ?? [];
    $items = $relationships['items'] ?? [];

    $items = array_map(
      fn($item) => [
        'id' => $item['id'],
        'quantity' => $item['quantity'],
      ],
      $items
    );

    return new self(
      shopping_name: $attributes['shopping_name'] ?? null,
      shopping_phone: $attributes['shopping_phone'] ?? null,
      shopping_city: $attributes['shopping_city'] ?? null,
      shopping_district: $attributes['shopping_district'] ?? null,
      shopping_ward: $attributes['shopping_ward'] ?? null,
      shopping_address_line: $attributes['shopping_address_line'] ?? null,
      method: $attributes['method'] ?? null,
      items: $items
    );
  }

  public function toArray(): array
  {
    $data = [];

    if ($this->shopping_name !== null) {
      $data['shopping_name'] = $this->shopping_name;
    }

    if ($this->shopping_phone !== null) {
      $data['shopping_phone'] = $this->shopping_phone;
    }

    if ($this->shopping_city !== null) {
      $data['shopping_city'] = $this->shopping_city;
    }

    if ($this->shopping_district !== null) {
      $data['shopping_district'] = $this->shopping_district;
    }

    if ($this->shopping_ward !== null) {
      $data['shopping_ward'] = $this->shopping_ward;
    }

    if ($this->shopping_address_line !== null) {
      $data['shopping_address_line'] = $this->shopping_address_line;
    }

    if ($this->method !== null) {
      $data['method'] = $this->method;
    }

    if (!empty($this->items)) {
      $data['items'] = $this->items;
    }

    return $data;
  }
}
