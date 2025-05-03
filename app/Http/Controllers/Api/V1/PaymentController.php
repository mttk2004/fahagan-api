<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ResponseMessage;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\OrderResource;
use App\Models\Order;
use App\Models\Payment;
use App\Services\VNPayService;
use App\Traits\HandleExceptions;
use App\Utils\ResponseUtils;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
  use HandleExceptions;

  public function __construct(
    private readonly VNPayService $vnpayService,
    private readonly string $entityName = 'payment'
  ) {}

  /**
   * Tạo URL thanh toán VNPay và chuyển hướng
   *
   * @param Order $order
   * @return JsonResponse
   */
  public function createVNPayPayment(Order $order): JsonResponse
  {
    // Kiểm tra xem đơn hàng có phải của người dùng hiện tại không
    if ($order->customer_id != auth()->id()) {
      return ResponseUtils::forbidden('Bạn không có quyền thanh toán đơn hàng này.');
    }

    // Kiểm tra phương thức thanh toán
    $payment = $order->payment;
    if (!$payment || $payment->method !== 'vnpay') {
      return ResponseUtils::badRequest('Đơn hàng không sử dụng phương thức thanh toán VNPay.');
    }

    // Kiểm tra trạng thái thanh toán
    if ($payment->status !== PaymentStatus::PENDING) {
      return ResponseUtils::badRequest('Đơn hàng đã được thanh toán hoặc đang trong quá trình thanh toán.');
    }

    try {
      // Tạo URL thanh toán VNPay
      $paymentUrl = $this->vnpayService->createPaymentUrl($order, $payment);

      // Cập nhật trạng thái thanh toán
      $payment->status = PaymentStatus::PROCESSING;
      $payment->save();

      return ResponseUtils::success([
        'payment_url' => $paymentUrl,
        'order_id' => $order->id
      ]);
    } catch (Exception $e) {
      return $this->handleException(
        $e,
        $this->entityName,
        ['order_id' => $order->id]
      );
    }
  }

  /**
   * Xử lý kết quả thanh toán từ VNPay
   *
   * @param Request $request
   * @return JsonResponse
   */
  public function handleVNPayReturn(Request $request): JsonResponse
  {
    try {
      // Lấy tất cả dữ liệu từ VNPay trả về
      $vnpayData = $request->all();

      // Ghi log dữ liệu nhận được
      Log::info('VNPay Return Data', $vnpayData);

      // Xử lý dữ liệu VNPay
      $result = $this->vnpayService->processReturnUrl($vnpayData);

      if (!$result['isValidSignature']) {
        return ResponseUtils::badRequest('Chữ ký không hợp lệ.');
      }

      // Tìm payment dựa trên transaction_ref
      $payment = Payment::where('transaction_ref', $result['txnRef'])->first();

      if (!$payment) {
        return ResponseUtils::notFound('Không tìm thấy thông tin thanh toán.');
      }

      // Tìm order tương ứng
      $order = $payment->order;

      if (!$order) {
        return ResponseUtils::notFound('Không tìm thấy đơn hàng.');
      }

      // Cập nhật trạng thái payment
      $payment->status = $result['paymentStatus'];
      $payment->gateway_response = $result['rawData'];
      $payment->save();

      // Nếu thanh toán thành công
      if ($result['paymentStatus'] === PaymentStatus::PAID) {
        // Cập nhật trạng thái đơn hàng
        $order->status = OrderStatus::APPROVED->value;
        $order->approved_at = now();
        $order->save();

        return ResponseUtils::success([
          'order' => new OrderResource($order->fresh()),
          'message' => 'Thanh toán thành công.'
        ], ResponseMessage::PAYMENT_SUCCESS->value);
      }

      return ResponseUtils::success([
        'order' => new OrderResource($order->fresh()),
        'message' => 'Thanh toán thất bại.'
      ], ResponseMessage::PAYMENT_FAILED->value);
    } catch (Exception $e) {
      return $this->handleException(
        $e,
        $this->entityName,
        ['vnpay_data' => $request->all()]
      );
    }
  }
}
