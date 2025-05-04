<?php

namespace Tests\Feature\Api\V1\Payment;

use App\Enums\PaymentStatus;
use App\Models\Address;
use App\Models\Book;
use App\Models\CartItem;
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

class VNPayPaymentTest extends TestCase
{
    use DatabaseTransactions;

    private User $customer;

    private Book $book;

    private CartItem $cartItem;

    private Order $order;

    private Payment $payment;

    private Address $address;

    private $mockVNPayService;

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

        // Tạo cart item
        $this->cartItem = CartItem::create([
          'user_id' => $this->customer->id,
          'book_id' => $this->book->id,
          'quantity' => 2,
        ]);

        // Tạo order
        $this->order = Order::create([
          'customer_id' => $this->customer->id,
          'shopping_name' => $this->address->name,
          'shopping_phone' => $this->address->phone,
          'shopping_city' => $this->address->city,
          'shopping_district' => $this->address->district,
          'shopping_ward' => $this->address->ward,
          'shopping_address_line' => $this->address->address_line,
        ]);

        // Tạo payment với method là vnpay
        $this->payment = $this->order->payment()->create([
          'method' => 'vnpay',
          'total_amount' => 200000, // 2 * 100000
          'status' => PaymentStatus::PENDING,
        ]);

        // Mock VNPayService
        $this->mockVNPayService = Mockery::mock(VNPayService::class);
        $this->instance(VNPayService::class, $this->mockVNPayService);
    }

    #[Test]
    public function it_can_create_vnpay_payment_url()
    {
        $this->mockVNPayService->shouldReceive('createPaymentUrl')
          ->once()
          ->withAnyArgs()
          ->andReturn('https://sandbox.vnpayment.vn/paymentv2/vpcpay.html?test=1');

        Sanctum::actingAs($this->customer, ['*']);

        $response = $this->getJson("/api/v1/customer/orders/{$this->order->id}/pay-vnpay");

        $response->assertStatus(200)
          ->assertJsonStructure([
            'data' => [
              'payment_url',
              'order_id',
            ],
          ]);

        // Kiểm tra trạng thái payment
        $this->payment->refresh();
        $this->assertEquals(PaymentStatus::PENDING, $this->payment->status);
    }

    #[Test]
    public function it_returns_error_if_order_does_not_belong_to_customer()
    {
        // Tạo khách hàng khác
        $otherCustomer = User::factory()->create(['is_customer' => true]);

        Sanctum::actingAs($otherCustomer, ['*']);

        $response = $this->getJson("/api/v1/customer/orders/{$this->order->id}/pay-vnpay");

        $response->assertStatus(403);
    }

    #[Test]
    public function it_returns_error_if_payment_method_is_not_vnpay()
    {
        // Cập nhật payment method thành cod
        $this->payment->update(['method' => 'cod']);

        Sanctum::actingAs($this->customer, ['*']);

        $response = $this->getJson("/api/v1/customer/orders/{$this->order->id}/pay-vnpay");

        $response->assertStatus(400);
    }

    #[Test]
    public function it_can_handle_vnpay_return()
    {
        // Lưu transaction_ref
        $txnRef = $this->order->id . '-' . time();
        $this->payment->transaction_ref = $txnRef;
        $this->payment->save();

        $this->mockVNPayService->shouldReceive('processReturnUrl')
          ->once()
          ->withAnyArgs()
          ->andReturn([
            'isValidSignature' => true,
            'paymentStatus' => PaymentStatus::PAID,
            'orderId' => $this->order->id,
            'txnRef' => $txnRef,
            'responseCode' => '00',
            'amount' => 200000,
            'rawData' => [
              'vnp_Amount' => 20000000,
              'vnp_ResponseCode' => '00',
              'vnp_TransactionStatus' => '00',
            ],
          ]);

        $response = $this->getJson("/api/v1/payment/vnpay-return?vnp_Amount=20000000&vnp_ResponseCode=00");

        $response->assertStatus(200)
          ->assertJsonStructure([
            'data' => [
              'redirect_url',
              'redirect_required',
            ],
          ]);

        // Kiểm tra trạng thái payment và order
        $this->payment->refresh();
        $this->order->refresh();

        $this->assertEquals(PaymentStatus::PAID, $this->payment->status);
    }

    #[Test]
    public function it_handles_invalid_signature_from_vnpay()
    {
        $this->mockVNPayService->shouldReceive('processReturnUrl')
          ->once()
          ->withAnyArgs()
          ->andReturn([
            'isValidSignature' => false,
            'paymentStatus' => PaymentStatus::FAILED,
            'orderId' => $this->order->id,
            'txnRef' => '',
            'responseCode' => '99',
            'amount' => 0,
            'rawData' => [],
          ]);

        $response = $this->getJson("/api/v1/payment/vnpay-return?invalid=true");

        $response->assertStatus(200)
          ->assertJsonStructure([
            'data' => [
              'redirect_url',
              'redirect_required',
            ],
          ]);
    }

    #[Test]
    public function it_handles_payment_not_found()
    {
        $this->mockVNPayService->shouldReceive('processReturnUrl')
          ->once()
          ->withAnyArgs()
          ->andReturn([
            'isValidSignature' => true,
            'paymentStatus' => PaymentStatus::PAID,
            'orderId' => 999,
            'txnRef' => 'non-existent-ref',
            'responseCode' => '00',
            'amount' => 200000,
            'rawData' => [],
          ]);

        $response = $this->getJson("/api/v1/payment/vnpay-return?vnp_Amount=20000000&vnp_ResponseCode=00");

        $response->assertStatus(200)
          ->assertJsonStructure([
            'data' => [
              'redirect_url',
              'redirect_required',
            ],
          ]);
    }

    #[Test]
    public function it_handles_failed_payment()
    {
        // Lưu transaction_ref
        $txnRef = $this->order->id . '-' . time();
        $this->payment->transaction_ref = $txnRef;
        $this->payment->save();

        $this->mockVNPayService->shouldReceive('processReturnUrl')
          ->once()
          ->withAnyArgs()
          ->andReturn([
            'isValidSignature' => true,
            'paymentStatus' => PaymentStatus::FAILED,
            'orderId' => $this->order->id,
            'txnRef' => $txnRef,
            'responseCode' => '24',
            'amount' => 200000,
            'rawData' => [
              'vnp_ResponseCode' => '24',
            ],
          ]);

        $response = $this->getJson("/api/v1/payment/vnpay-return?vnp_Amount=20000000&vnp_ResponseCode=24");

        $response->assertStatus(200)
          ->assertJsonStructure([
            'data' => [
              'redirect_url',
              'redirect_required',
            ],
          ]);

        // Kiểm tra trạng thái payment
        $this->payment->refresh();
        $this->assertEquals(PaymentStatus::FAILED, $this->payment->status);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
