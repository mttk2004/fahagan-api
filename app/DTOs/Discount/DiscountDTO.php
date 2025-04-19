<?php

namespace App\DTOs\Discount;

class DiscountDTO extends \App\DTOs\BaseDTO
{
  public function __construct(
    public readonly ?string $name,
    public readonly ?string $discount_type,
    public readonly ?float $discount_value,
    public readonly ?string $target_type,
    public readonly ?string $start_date,
    public readonly ?string $end_date,
    public readonly ?string $description = null,
    public readonly ?bool $is_active = true,
    public readonly array $target_ids = [],
  ) {}

  public static function fromRequest(array $validatedData): self
  {
    $attributes = $validatedData['data']['attributes'] ?? [];
    $relationships = $validatedData['data']['relationships'] ?? [];

    // Lấy target_ids từ relationships nếu có
    $target_ids = [];
    if (isset($relationships['targets']['data'])) {
      $target_ids = collect($relationships['targets']['data'])->pluck('id')->toArray();
    }

    return new self(
      name: $attributes['name'] ?? null,
      discount_type: $attributes['discount_type'] ?? null,
      discount_value: isset($attributes['discount_value']) ? (float)$attributes['discount_value'] : null,
      target_type: $attributes['target_type'] ?? 'book',
      start_date: $attributes['start_date'] ?? null,
      end_date: $attributes['end_date'] ?? null,
      description: $attributes['description'] ?? null,
      is_active: $attributes['is_active'] ?? true,
      target_ids: $target_ids,
    );
  }

  public function toArray(): array
  {
    $data = [];

    if ($this->name !== null) {
      $data['name'] = $this->name;
    }

    if ($this->discount_type !== null) {
      $data['discount_type'] = $this->discount_type;
    }

    if ($this->discount_value !== null) {
      $data['discount_value'] = $this->discount_value;
    }

    if ($this->target_type !== null) {
      $data['target_type'] = $this->target_type;
    }

    if ($this->start_date !== null) {
      $data['start_date'] = $this->start_date;
    }

    if ($this->end_date !== null) {
      $data['end_date'] = $this->end_date;
    }

    if ($this->description !== null) {
      $data['description'] = $this->description;
    }

    if ($this->is_active !== null) {
      $data['is_active'] = $this->is_active;
    }

    return $data;
  }
}
