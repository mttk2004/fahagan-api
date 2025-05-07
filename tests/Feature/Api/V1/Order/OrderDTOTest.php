<?php

namespace Tests\Feature\Api\V1\Order;

use App\DTOs\OrderDTO;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderDTOTest extends TestCase
{
    #[Test]
    public function it_can_create_order_dto()
    {
        $orderDTO = new OrderDTO('cod', 1);
        $this->assertEquals('cod', $orderDTO->method);
        $this->assertEquals(1, $orderDTO->address_id);
    }
}
