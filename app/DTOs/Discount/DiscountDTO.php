<?php

namespace App\DTOs\Discount;

class DiscountDTO extends \App\DTOs\BaseDTO
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?string $discount_type,
        public readonly ?float $discount_value,
        public readonly ?string $start_date,
        public readonly ?string $end_date,
        public readonly array $target_ids = [],
    ) {
    }

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
            start_date: $attributes['start_date'] ?? null,
            end_date: $attributes['end_date'] ?? null,
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

        if ($this->start_date !== null) {
            $data['start_date'] = $this->start_date;
        }

        if ($this->end_date !== null) {
            $data['end_date'] = $this->end_date;
        }

        return $data;
    }
}
