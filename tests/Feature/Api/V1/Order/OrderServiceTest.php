<?php

namespace Tests\Feature\Api\V1\Order;

use App\Enums\OrderStatus;
use App\Models\Book;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
  use RefreshDatabase;

  private User $customer;

  private User $employee;

  private Book $book;

  private CartItem $cartItem;

  private Order $order;

  private OrderService $orderService;

  protected function setUp(): void
  {
    parent::setUp();

    // Tạo người dùng customer
    $this->customer = User::factory()->create([
      'is_customer' => true,
    ]);

    // Tạo nhân viên
    $this->employee = User::factory()->create([
      'is_customer' => false,
    ]);

    // Tạo một sách để test với số lượng đủ
    $this->book = Book::factory()->create([
      'available_count' => 10,
      'sold_count' => 0,
    ]);

    // Thêm sản phẩm vào giỏ hàng
    $this->cartItem = CartItem::create([
      'user_id' => $this->customer->id,
      'book_id' => $this->book->id,
      'quantity' => 2,
    ]);

    // Tạo đơn hàng mới
    $this->order = Order::create([
      'customer_id' => $this->customer->id,
      'shopping_name' => 'Test Customer',
      'shopping_phone' => '0938244325',
      'shopping_city' => 'HCM',
      'shopping_district' => '1',
      'shopping_ward' => '1',
      'shopping_address_line' => '123 Test Street',
      'status' => OrderStatus::PENDING->value,
    ]);

    // Tạo order item
    $this->order->items()->create([
      'book_id' => $this->book->id,
      'quantity' => 2,
      'price_at_time' => $this->book->price,
      'discount_value' => 0,
    ]);

    // Khởi tạo OrderService
    $this->orderService = app(OrderService::class);
  }

  #[Test]
  public function it_gets_customer_orders()
  {
    Sanctum::actingAs($this->customer);

    // Tạo thêm 2 đơn hàng khác cho customer này
    for ($i = 0; $i < 2; $i++) {
      Order::create([
        'customer_id' => $this->customer->id,
        'shopping_name' => 'Test Customer',
        'shopping_phone' => '0938244325',
        'shopping_city' => 'HCM',
        'shopping_district' => '1',
        'shopping_ward' => '1',
        'shopping_address_line' => '123 Test Street',
      ]);
    }

    // Tạo một khách hàng khác và một đơn hàng
    $otherCustomer = User::factory()->create(['is_customer' => true]);
    Order::create([
      'customer_id' => $otherCustomer->id,
      'shopping_name' => 'Other Customer',
      'shopping_phone' => '0938244326',
      'shopping_city' => 'HN',
      'shopping_district' => '2',
      'shopping_ward' => '2',
      'shopping_address_line' => '456 Test Street',
    ]);

    // Gọi phương thức getCustomerOrders
    $request = new Request;
    $customerOrders = $this->orderService->getCustomerOrders($request, 10);

    // Kiểm tra kết quả
    $this->assertEquals(3, $customerOrders->total());
    $this->assertEquals($this->customer->id, $customerOrders[0]->customer_id);
  }

  #[Test]
  public function it_gets_order_details()
  {
    $orderDetails = $this->orderService->getOrderDetails($this->order->id);

    $this->assertEquals($this->order->id, $orderDetails->id);
    $this->assertEquals($this->customer->id, $orderDetails->customer_id);
    $this->assertEquals(1, $orderDetails->items->count());
  }

  #[Test]
  public function it_cancels_order_in_pending_status()
  {
    // Gọi phương thức cancelOrder
    $cancelledOrder = $this->orderService->cancelOrder($this->order->id);

    // Kiểm tra kết quả
    $this->assertEquals(OrderStatus::CANCELED->value, $cancelledOrder->status);
    $this->assertNotNull($cancelledOrder->canceled_at);
  }

  #[Test]
  public function it_completes_order_in_delivered_status()
  {
    // Cập nhật trạng thái đơn hàng thành DELIVERED
    $this->order->status = OrderStatus::DELIVERED->value;
    $this->order->save();

    // Gọi phương thức completeOrder
    $completedOrder = $this->orderService->completeOrder($this->order->id);

    // Kiểm tra kết quả
    $this->assertEquals(OrderStatus::COMPLETED->value, $completedOrder->status);
    $this->assertNotNull($completedOrder->completed_at);
  }

  #[Test]
  public function it_updates_book_quantities_when_order_is_approved()
  {
    Sanctum::actingAs($this->employee);

    // Lấy số lượng sách ban đầu
    $initialAvailableCount = $this->book->available_count;
    $initialSoldCount = $this->book->sold_count;

    // Cập nhật trạng thái đơn hàng từ PENDING sang APPROVED
    $updatedOrder = $this->orderService->updateOrderStatus($this->order->id, OrderStatus::APPROVED->value);

    // Refresh book model
    $this->book->refresh();

    // Kiểm tra kết quả
    $this->assertEquals(OrderStatus::APPROVED->value, $updatedOrder->status);
    $this->assertNotNull($updatedOrder->approved_at);
    $this->assertEquals($this->employee->id, $updatedOrder->employee_id);

    // Kiểm tra số lượng sách đã được cập nhật
    $orderItem = $this->order->items->first();
    $this->assertEquals($initialAvailableCount - $orderItem->quantity, $this->book->available_count);
    $this->assertEquals($initialSoldCount + $orderItem->quantity, $this->book->sold_count);
  }

  #[Test]
  public function it_does_not_update_book_quantities_for_other_status_changes()
  {
    Sanctum::actingAs($this->employee);

    // Lấy số lượng sách ban đầu
    $initialAvailableCount = $this->book->available_count;
    $initialSoldCount = $this->book->sold_count;

    // Đầu tiên cập nhật sang APPROVED để có thể cập nhật sang DELIVERED
    $this->order->status = OrderStatus::APPROVED->value;
    $this->order->save();

    // Cập nhật trạng thái đơn hàng từ APPROVED sang DELIVERING
    $updatedOrder = $this->orderService->updateOrderStatus($this->order->id, OrderStatus::DELIVERING->value);

    // Refresh book model
    $this->book->refresh();

    // Kiểm tra kết quả - số lượng sách không thay đổi khi chuyển trạng thái
    // từ APPROVED sang DELIVERED
    $this->assertEquals(OrderStatus::DELIVERING->value, $updatedOrder->status);
    $this->assertEquals($initialAvailableCount, $this->book->available_count);
    $this->assertEquals($initialSoldCount, $this->book->sold_count);
  }

  #[Test]
  public function it_prevents_invalid_status_transitions()
  {
    Sanctum::actingAs($this->employee);

    // Thử cập nhật từ PENDING sang DELIVERED (không hợp lệ)
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Không thể cập nhật từ trạng thái');

    $this->orderService->updateOrderStatus($this->order->id, OrderStatus::DELIVERED->value);
  }
}
