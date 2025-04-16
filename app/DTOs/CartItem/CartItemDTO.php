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
    $attributes = $validatedData['data']['attributes'] ?? [];

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
