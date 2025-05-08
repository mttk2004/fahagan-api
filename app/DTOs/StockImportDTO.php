<?php

namespace App\DTOs;

class StockImportDTO extends BaseDTO
{
  public function __construct(
    public readonly ?float $discount_value,
    public readonly ?int $supplier_id,
    public readonly ?int $employee_id,
    public readonly ?array $items,
  ) {}

  public static function fromRequest(array $validatedData): self
  {
    $attributes = $validatedData['data']['attributes'];
    $relationships = $validatedData['data']['relationships'];

    $items = $relationships['items']['data'];

    $items = collect($items)->map(function ($item) {
      return StockImportItemDTO::fromRequest($item);
    })->toArray();

    return new self(
      discount_value: $attributes['discount_value'] ?? null,
      supplier_id: $relationships['supplier']['data']['id'] ?? null,
      employee_id: $relationships['employee']['data']['id'] ?? null,
      items: $items,
    );
  }

  public function toArray(): array
  {
    $data = [];

    if ($this->discount_value !== null) {
      $data['discount_value'] = $this->discount_value;
    }

    if ($this->supplier_id !== null) {
      $data['supplier_id'] = $this->supplier_id;
    }

    if ($this->employee_id !== null) {
      $data['employee_id'] = $this->employee_id;
    }

    if ($this->items !== null) {
      $data['items'] = $this->items;
    }

    return $data;
  }
}
