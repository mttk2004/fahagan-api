<?php

namespace Tests\Feature\Api\V1\CartItem;

use App\DTOs\CartItem\CartItemDTO;
use PHPUnit\Framework\TestCase;

class CartItemDTOTest extends TestCase
{
    public function test_it_creates_cart_item_dto_from_request()
    {
        $validatedData = [
          'book_id' => 123,
          'quantity' => 2,
        ];

        $cartItemDTO = CartItemDTO::fromRequest($validatedData);

        $this->assertEquals(123, $cartItemDTO->book_id);
        $this->assertEquals(2, $cartItemDTO->quantity);
    }

    public function test_it_converts_to_array()
    {
        $cartItemDTO = new CartItemDTO(
            book_id: 456,
            quantity: 3
        );

        $array = $cartItemDTO->toArray();

        $this->assertIsArray($array);
        $this->assertEquals(456, $array['book_id']);
        $this->assertEquals(3, $array['quantity']);
    }
}
