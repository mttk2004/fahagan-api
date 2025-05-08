<?php

namespace App\DTOs;

class StockImportItemDTO extends BaseDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly ?int $quantity = 1,
        public readonly ?float $unit_price,
    ) {
    }

    public static function fromRequest(array $validatedData): self
    {
        return new self(
            id: $validatedData['id'] ?? null,
            quantity: $validatedData['quantity'] ?? null,
            unit_price: $validatedData['unit_price'] ?? null,
        );
    }

    public function toArray(): array
    {
        $data = [];

        if ($this->id !== null) {
            $data['id'] = $this->id;
        }

        if ($this->quantity !== null) {
            $data['quantity'] = $this->quantity;
        }

        if ($this->unit_price !== null) {
            $data['unit_price'] = $this->unit_price;
        }

        return $data;
    }
}
