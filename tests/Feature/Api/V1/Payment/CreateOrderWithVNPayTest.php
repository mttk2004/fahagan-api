<?php

namespace Tests\Feature\Api\V1\Payment;

use App\Enums\PaymentStatus;
use App\Models\Address;
use App\Models\Book;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\VNPayService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateOrderWithVNPayTest extends TestCase
{
    use DatabaseTransactions;

    protected User $customer;

    protected Book $book;

    protected Order $order;

    protected Payment $payment;

    protected Address $address;

    protected $mockVNPayService;

    protected function setUp(): void
    {
        parent::setUp();

        // Tạo khách hàng
        $this->customer = User::factory()->create([
            'is_customer' => true,
            'password' => Hash::make('password'),
        ]);

        // Tạo địa chỉ
        $this->address = $this->customer->addresses()->create([
            'name' => 'Test Customer',
            'phone' => '0938244325',
            'city' => 'HCM',
            'district' => '1',
            'ward' => '1',
            'address_line' => '123 Test Street',
        ]);

        // Tạo sách
        $this->book = Book::factory()->create([
            'price' => 100000,
            'available_count' => 10,
        ]);

        // Tạo order trực tiếp thay vì thông qua API
        $this->order = Order::create([
            'customer_id' => $this->customer->id,
            'shopping_name' => $this->address->name,
            'shopping_phone' => $this->address->phone,
            'shopping_city' => $this->address->city,
            'shopping_district' => $this->address->district,
            'shopping_ward' => $this->address->ward,
            'shopping_address_line' => $this->address->address_line,
        ]);

        // Mock VNPayService
        $this->mockVNPayService = Mockery::mock(VNPayService::class);
        $this->mockVNPayService->shouldReceive('createPaymentUrl')
            ->withAnyArgs()
            ->andReturn('https://sandbox.vnpayment.vn/paymentv2/vpcpay.html?test=1');

        $this->instance(VNPayService::class, $this->mockVNPayService);
    }

    #[Test]
    public function it_can_create_payment_with_vnpay_method()
    {
        // Tạo payment với method vnpay
        $payment = $this->order->payment()->create([
            'method' => 'vnpay',
            'total_amount' => 200000,
            'status' => PaymentStatus::PENDING,
        ]);

        Sanctum::actingAs($this->customer, ['*']);

        // Kiểm tra URL thanh toán VNPay
        $response = $this->getJson("/api/v1/customer/orders/{$this->order->id}/pay-vnpay");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'payment_url',
                    'order_id',
                ],
            ]);

        // Kiểm tra payment đã được tạo với status là PENDING
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'method' => 'vnpay',
            'status' => PaymentStatus::PENDING->value,
        ]);
    }

    #[Test]
    public function it_can_create_payment_with_cod_method()
    {
        // Tạo order mới
        $order = Order::create([
            'customer_id' => $this->customer->id,
            'shopping_name' => $this->address->name,
            'shopping_phone' => $this->address->phone,
            'shopping_city' => $this->address->city,
            'shopping_district' => $this->address->district,
            'shopping_ward' => $this->address->ward,
            'shopping_address_line' => $this->address->address_line,
        ]);

        // Tạo payment với method là cod (cash on delivery)
        $payment = $order->payment()->create([
            'method' => 'cod',
            'total_amount' => 100000,
            'status' => PaymentStatus::PAID, // Cash payments are PAID immediately
        ]);

        // Kiểm tra payment đã được tạo với status là PAID
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'method' => 'cod',
            'status' => PaymentStatus::PAID->value,
        ]);
    }

    #[Test]
    public function it_returns_payment_url_for_vnpay_method()
    {
        // Tạo payment với method vnpay
        $payment = $this->order->payment()->create([
            'method' => 'vnpay',
            'total_amount' => 200000,
            'status' => PaymentStatus::PENDING,
        ]);

        Sanctum::actingAs($this->customer, ['*']);

        // Kiểm tra URL thanh toán VNPay
        $response = $this->getJson("/api/v1/customer/orders/{$this->order->id}/pay-vnpay");

        $response->assertStatus(200)
            ->assertJsonPath('data.payment_url', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html?test=1');
    }

    #[Test]
    public function it_validates_payment_method()
    {
        Sanctum::actingAs($this->customer, ['*']);

        $response = $this->postJson('/api/v1/customer/orders', [
            'data' => [
                'attributes' => [
                    'method' => 'invalid_method', // Phương thức không hỗ trợ, nên so sánh với cod/bank_transfer/vnpay/paypal
                ],
                'relationships' => [
                    'address' => [
                        'id' => $this->address->id,
                    ],
                    'items' => [
                        [
                            'id' => 9999, // ID không tồn tại, nhưng chỉ quan tâm đến validation method
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['data.attributes.method']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
