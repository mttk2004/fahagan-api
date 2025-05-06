<?php

namespace App\DTOs\CartItem;

class CartItemDTO extends \App\DTOs\BaseDTO
{
    public function __construct(
        public readonly int $book_id,
        public readonly int $quantity,
    ) {}

    public static function fromRequest(array $validatedData): self
    {
        return new self(
            book_id: $validatedData['book_id'],
            quantity: $validatedData['quantity'],
        );
    }

    public function toArray(): array
    {
        return [
            'book_id' => $this->book_id,
            'quantity' => $this->quantity,
        ];
    }
}
