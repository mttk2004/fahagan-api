<?php

namespace Tests\Feature\Api\V1\Order;

use App\Enums\OrderStatus;
use App\Models\Book;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\TestPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class OrderControllerTest extends TestCase
{
  use RefreshDatabase;

  private User $employee;
  private User $customer;
  private Book $book;
  private Order $order;

  protected function setUp(): void
  {
    parent::setUp();

    // Chạy seeder để tạo các quyền cần thiết
    $this->seed(TestPermissionSeeder::class);

    // Tạo người dùng employee (không phải customer)
    $this->employee = User::factory()->create([
      'is_customer' => false,
      'password' => Hash::make('password'),
    ]);

    // Tạo role Sales Staff và gán cho employee
    Role::create(['name' => 'Sales Staff']);
    $this->employee->assignRole('Sales Staff');

    // Tạo người dùng customer
    $this->customer = User::factory()->create([
      'is_customer' => true,
      'password' => Hash::make('password'),
    ]);

    // Tạo một sách để test với số lượng đủ
    $this->book = Book::factory()->create([
      'available_count' => 10,
      'sold_count' => 0,
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
  }

  #[Test]
  public function employee_can_view_all_orders()
  {
    // Đảm bảo người dùng có quyền xem đơn hàng
    $this->employee->givePermissionTo('view_orders');
    Sanctum::actingAs($this->employee);

    // Tạo thêm 2 đơn hàng khác
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

    // Gọi API để lấy danh sách đơn hàng
    $response = $this->getJson('/api/v1/orders');

    $response->assertStatus(200)
      ->assertJsonCount(3, 'data');
  }

  #[Test]
  public function employee_can_view_specific_order()
  {
    // Đảm bảo người dùng có quyền xem đơn hàng
    $this->employee->givePermissionTo('view_orders');
    Sanctum::actingAs($this->employee);

    // Gọi API để lấy chi tiết đơn hàng
    $response = $this->getJson("/api/v1/orders/{$this->order->id}");

    // For debugging
    if ($response->getStatusCode() !== 200) {
      Log::debug('Response content: ' . $response->getContent());
    }

    $response->assertStatus(200);

    // Kiểm tra thuộc tính đơn hàng nằm trong attributes
    $this->assertEquals($this->order->id, $response->json('data.order.id'));
    $this->assertEquals($this->customer->id, $response->json('data.order.attributes.customer_id'));
  }

  #[Test]
  public function employee_can_update_order_status_from_pending_to_approved()
  {
    // Đảm bảo người dùng có quyền chỉnh sửa đơn hàng
    $this->employee->givePermissionTo('edit_orders');
    Sanctum::actingAs($this->employee);

    // Gọi API để cập nhật trạng thái đơn hàng từ PENDING sang APPROVED
    $response = $this->patchJson("/api/v1/orders/{$this->order->id}/status", [
      'status' => OrderStatus::APPROVED->value,
    ]);

    // For debugging
    if ($response->getStatusCode() !== 200) {
      Log::debug('Response content: ' . $response->getContent());
    }

    // Làm mới dữ liệu sách và đơn hàng
    $this->book->refresh();
    $this->order->refresh();

    $response->assertStatus(200);

    // Kiểm tra thuộc tính trong attributes và database
    $this->assertEquals(OrderStatus::APPROVED->value, $this->order->status);
    $this->assertEquals($this->employee->id, $this->order->employee_id);
    $this->assertNotNull($this->order->approved_at);

    // Kiểm tra số lượng sách đã được cập nhật
    $this->assertEquals(8, $this->book->available_count);
    $this->assertEquals(2, $this->book->sold_count);
  }

  #[Test]
  public function employee_can_update_order_status_from_approved_to_delivered()
  {
    // Đảm bảo người dùng có quyền chỉnh sửa đơn hàng
    $this->employee->givePermissionTo('edit_orders');
    Sanctum::actingAs($this->employee);

    // Đầu tiên, chuyển trạng thái đơn hàng sang APPROVED
    $this->order->status = OrderStatus::APPROVED->value;
    $this->order->employee_id = $this->employee->id;
    $this->order->approved_at = now();
    $this->order->save();

    // Gọi API để cập nhật trạng thái đơn hàng từ APPROVED sang DELIVERED
    $response = $this->patchJson("/api/v1/orders/{$this->order->id}/status", [
      'status' => OrderStatus::DELIVERED->value,
    ]);

    // For debugging
    if ($response->getStatusCode() !== 200) {
      Log::debug('Response content: ' . $response->getContent());
    }

    $response->assertStatus(200);

    // Refresh order để lấy dữ liệu mới nhất từ database
    $this->order->refresh();

    // Kiểm tra thuộc tính từ database thay vì response
    $this->assertEquals(OrderStatus::DELIVERED->value, $this->order->status);
    $this->assertNotNull($this->order->delivered_at, 'delivered_at chưa được cập nhật trong database');
  }

  #[Test]
  public function employee_cannot_update_order_status_with_invalid_transition()
  {
    // Đảm bảo người dùng có quyền chỉnh sửa đơn hàng
    $this->employee->givePermissionTo('edit_orders');
    Sanctum::actingAs($this->employee);

    // Gọi API để cập nhật trạng thái đơn hàng từ PENDING sang DELIVERED (không hợp lệ)
    $response = $this->patchJson("/api/v1/orders/{$this->order->id}/status", [
      'status' => OrderStatus::DELIVERED->value,
    ]);

    $response->assertStatus(500);
  }

  #[Test]
  public function employee_without_permission_cannot_update_order_status()
  {
    // Không cấp quyền cho nhân viên
    Sanctum::actingAs($this->employee);

    // Gọi API để cập nhật trạng thái đơn hàng
    $response = $this->patchJson("/api/v1/orders/{$this->order->id}/status", [
      'status' => 'APPROVED',
    ]);

    $response->assertStatus(403);
  }
}
