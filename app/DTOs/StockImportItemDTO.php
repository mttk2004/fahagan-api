<?php

namespace App\DTOs;

class StockImportItemDTO extends BaseDTO
{
    public function __construct(
        public readonly ?int $book_id,
        public readonly ?int $quantity = 1,
        public readonly ?float $unit_price,
    ) {
    }

    public static function fromRequest(array $validatedData): self
    {
        return new self(
            book_id: $validatedData['book_id'] ?? null,
            quantity: $validatedData['quantity'] ?? null,
            unit_price: $validatedData['unit_price'] ?? null,
        );
    }

    public function toArray(): array
    {
        $data = [];

        if ($this->book_id !== null) {
            $data['book_id'] = $this->book_id;
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
