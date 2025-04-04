<?php

namespace App\DTOs\CartItem;

class CartItemDTO
{
    public function __construct(
        public readonly int $book_id,
        public readonly int $quantity,
    ) {
    }

    public static function fromRequest(array $validatedData): self
    {
        // Handle both JSON:API format and direct format
        $attributes = $validatedData;

        // Check if we have a JSON:API format
        if (isset($validatedData['data']) && isset($validatedData['data']['attributes'])) {
            $attributes = $validatedData['data']['attributes'];
        }

        return new self(
            book_id: $attributes['book_id'],
            quantity: $attributes['quantity'],
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
