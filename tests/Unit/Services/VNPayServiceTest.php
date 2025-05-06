<?php

namespace Tests\Unit\Services;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Services\VNPayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VNPayServiceTest extends TestCase
{
    use RefreshDatabase;

    private VNPayService $vnpayService;

    private Order $order;

    private Payment $payment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->vnpayService = new VNPayService;

        // Cấu hình VNPay test
        Config::set('vnpay.tmnCode', 'VAAJN51S');
        Config::set('vnpay.hashSecret', 'UNOBMR165GLWAXUC51RO1I89FWIBH6V8');
        Config::set('vnpay.url', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
        Config::set('vnpay.returnUrl', 'http://localhost:8000/api/v1/payments/vnpay-return');
        Config::set('vnpay.version', '2.1.0');

        // Tạo order và payment
        $this->order = Order::factory()->create();
        $this->payment = Payment::create([
            'order_id' => $this->order->id,
            'method' => 'vnpay',
            'total_amount' => 100000,
            'status' => PaymentStatus::PENDING,
        ]);
    }

    #[Test]
    public function it_can_create_payment_url()
    {
        $paymentUrl = $this->vnpayService->createPaymentUrl($this->order, $this->payment);

        // Kiểm tra URL có chứa các tham số cần thiết
        $this->assertStringContainsString('vnp_Amount=10000000', $paymentUrl); // 100000 * 100
        $this->assertStringContainsString('vnp_Command=pay', $paymentUrl);
        $this->assertStringContainsString('vnp_CurrCode=VND', $paymentUrl);
        $this->assertStringContainsString('vnp_TmnCode=VAAJN51S', $paymentUrl);
        $this->assertStringContainsString('vnp_Version=2.1.0', $paymentUrl);
        $this->assertStringContainsString('vnp_SecureHash=', $paymentUrl);

        // Kiểm tra transaction_ref được lưu
        $this->payment->refresh();
        $this->assertNotNull($this->payment->transaction_ref);
        $this->assertStringContainsString($this->order->id.'-', $this->payment->transaction_ref);
    }

    #[Test]
    public function it_can_process_valid_return_url()
    {
        // Tạo transaction_ref cho payment
        $txnRef = $this->order->id.'-'.time();
        $this->payment->transaction_ref = $txnRef;
        $this->payment->save();

        // Mô phỏng dữ liệu trả về từ VNPay
        $secureHash = hash_hmac(
            'sha512',
            'vnp_Amount=10000000&vnp_ResponseCode=00&vnp_TransactionStatus=00&vnp_TxnRef='.$txnRef,
            Config::get('vnpay.hashSecret')
        );

        $returnData = [
            'vnp_Amount' => 10000000,
            'vnp_ResponseCode' => '00',
            'vnp_TransactionStatus' => '00',
            'vnp_TxnRef' => $txnRef,
            'vnp_SecureHash' => $secureHash,
        ];

        // Xử lý dữ liệu trả về
        $result = $this->vnpayService->processReturnUrl($returnData);

        // Kiểm tra kết quả
        $this->assertTrue($result['isValidSignature']);
        $this->assertEquals(PaymentStatus::PAID, $result['paymentStatus']);
        $this->assertEquals($this->order->id, $result['orderId']);
        $this->assertEquals($txnRef, $result['txnRef']);
        $this->assertEquals('00', $result['responseCode']);
        $this->assertEquals(100000, $result['amount']);
    }

    #[Test]
    public function it_handles_invalid_hash()
    {
        // Mô phỏng dữ liệu với hash không hợp lệ
        $returnData = [
            'vnp_Amount' => 10000000,
            'vnp_ResponseCode' => '00',
            'vnp_TransactionStatus' => '00',
            'vnp_TxnRef' => $this->order->id.'-1234567890',
            'vnp_SecureHash' => 'invalid_hash',
        ];

        // Xử lý dữ liệu
        $result = $this->vnpayService->processReturnUrl($returnData);

        // Kiểm tra kết quả
        $this->assertFalse($result['isValidSignature']);
        $this->assertEquals(PaymentStatus::FAILED, $result['paymentStatus']);
    }

    #[Test]
    public function it_handles_failed_payment()
    {
        // Tạo transaction_ref cho payment
        $txnRef = $this->order->id.'-'.time();
        $this->payment->transaction_ref = $txnRef;
        $this->payment->save();

        // Mô phỏng dữ liệu thất bại từ VNPay
        $secureHash = hash_hmac(
            'sha512',
            'vnp_Amount=10000000&vnp_ResponseCode=24&vnp_TransactionStatus=02&vnp_TxnRef='.$txnRef,
            Config::get('vnpay.hashSecret')
        );

        $returnData = [
            'vnp_Amount' => 10000000,
            'vnp_ResponseCode' => '24', // Customer cancelled
            'vnp_TransactionStatus' => '02',
            'vnp_TxnRef' => $txnRef,
            'vnp_SecureHash' => $secureHash,
        ];

        // Xử lý dữ liệu trả về
        $result = $this->vnpayService->processReturnUrl($returnData);

        // Kiểm tra kết quả
        $this->assertTrue($result['isValidSignature']);
        $this->assertEquals(PaymentStatus::FAILED, $result['paymentStatus']);
        $this->assertEquals('24', $result['responseCode']);
    }

    #[Test]
    public function it_extracts_order_id_from_txnref()
    {
        // Tạo transaction_ref
        $txnRef = $this->order->id.'-1234567890';

        // Mô phỏng dữ liệu trả về
        $secureHash = hash_hmac(
            'sha512',
            'vnp_Amount=10000000&vnp_ResponseCode=00&vnp_TransactionStatus=00&vnp_TxnRef='.$txnRef,
            Config::get('vnpay.hashSecret')
        );

        $returnData = [
            'vnp_Amount' => 10000000,
            'vnp_ResponseCode' => '00',
            'vnp_TransactionStatus' => '00',
            'vnp_TxnRef' => $txnRef,
            'vnp_SecureHash' => $secureHash,
        ];

        // Xử lý dữ liệu
        $result = $this->vnpayService->processReturnUrl($returnData);

        // Kiểm tra order_id được trích xuất đúng
        $this->assertEquals($this->order->id, $result['orderId']);
    }
}
