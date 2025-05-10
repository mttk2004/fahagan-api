<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\PaymentStatus;
use App\Enums\ResponseMessage;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\VNPayService;
use App\Traits\HandleExceptions;
use App\Utils\AuthUtils;
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
   * @group Payment
   * @authenticated
   */
  public function createVNPayPayment(Order $order): JsonResponse
  {
    // Kiểm tra xem đơn hàng có phải của người dùng hiện tại không
    if ($order->customer_id != AuthUtils::user()->id) {
      return ResponseUtils::forbidden(ResponseMessage::FORBIDDEN->value);
    }

    // Kiểm tra phương thức thanh toán
    $payment = $order->payment;
    if (! $payment || $payment->method !== 'vnpay') {
      return ResponseUtils::badRequest(ResponseMessage::INVALID_PAYMENT_METHOD->value);
    }

    // Kiểm tra trạng thái thanh toán
    if ($payment->status !== PaymentStatus::PENDING) {
      return ResponseUtils::badRequest(ResponseMessage::PAYMENT_PROCESSING->value);
    }

    try {
      // Tạo URL thanh toán VNPay
      $paymentUrl = $this->vnpayService->createPaymentUrl($order, $payment);

      // Cập nhật trạng thái thanh toán
      $payment->status = PaymentStatus::PENDING;
      $payment->save();

      return ResponseUtils::success([
        'payment_url' => $paymentUrl,
        'order_id' => $order->id,
      ], ResponseMessage::PAYMENT_PENDING->value);
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
   * @return \Illuminate\Http\Response
   * @group Payment
   * @authenticated
   */
  public function handleVNPayReturn(Request $request)
  {
    try {
      // Lấy tất cả dữ liệu từ VNPay trả về
      $vnpayData = $request->all();

      // Ghi log dữ liệu nhận được
      Log::info('VNPay Return Data', $vnpayData);

      // Xử lý dữ liệu VNPay
      $result = $this->vnpayService->processReturnUrl($vnpayData);

      if (! $result['isValidSignature']) {
        // Chuyển hướng đến trang thất bại với thông báo lỗi
        $clientFailedUrl = config('vnpay.clientFailedUrl') . '?message=' . urlencode(ResponseMessage::INVALID_PAYMENT_SIGNATURE->value);

        return $this->redirectWithBrowserSupport($clientFailedUrl);
      }

      // Tìm payment dựa trên transaction_ref
      $payment = Payment::where('transaction_ref', $result['txnRef'])->first();

      if (! $payment) {
        // Chuyển hướng đến trang thất bại với thông báo lỗi
        $clientFailedUrl = config('vnpay.clientFailedUrl') . '?message=' . urlencode(ResponseMessage::NOT_FOUND_PAYMENT->value);

        return $this->redirectWithBrowserSupport($clientFailedUrl);
      }

      // Tìm order tương ứng
      $order = $payment->order;

      if (! $order) {
        // Chuyển hướng đến trang thất bại với thông báo lỗi
        $clientFailedUrl = config('vnpay.clientFailedUrl') . '?message=' . urlencode(ResponseMessage::NOT_FOUND_ORDER->value);

        return $this->redirectWithBrowserSupport($clientFailedUrl);
      }

      // Cập nhật trạng thái payment
      $payment->status = $result['paymentStatus'];
      $payment->gateway_response = $result['rawData'];
      $payment->save();

      // Nếu thanh toán thành công
      if ($result['paymentStatus'] === PaymentStatus::PAID) {
        // Không tự động cập nhật trạng thái đơn hàng thành APPROVED
        // Đơn hàng vẫn ở trạng thái PENDING chờ nhân viên xét duyệt

        // Chuyển hướng đến trang thành công với thông tin đơn hàng
        $clientSuccessUrl = config('vnpay.clientSuccessUrl') . '?orderId=' . $order->id;

        return $this->redirectWithBrowserSupport($clientSuccessUrl);
      }

      // Nếu thanh toán thất bại, chuyển hướng đến trang thất bại
      $clientFailedUrl = config('vnpay.clientFailedUrl') . '?orderId=' . $order->id;

      return $this->redirectWithBrowserSupport($clientFailedUrl);
    } catch (Exception $e) {
      Log::error('VNPay Payment Error', [
        'error' => $e->getMessage(),
        'vnpay_data' => $request->all(),
      ]);

      // Chuyển hướng đến trang thất bại với thông báo lỗi
      $clientFailedUrl = config('vnpay.clientFailedUrl') . '?message=' . urlencode('Đã xảy ra lỗi khi xử lý thanh toán');

      return $this->redirectWithBrowserSupport($clientFailedUrl);
    }
  }

  /**
   * Hàm hỗ trợ chuyển hướng với cả API response và browser redirect
   *
   * @param  string  $url  URL đích để chuyển hướng
   * @return \Illuminate\Http\Response
   */
  private function redirectWithBrowserSupport(string $url)
  {
    // Kiểm tra nếu là request API (có Accept: application/json)
    if (request()->expectsJson()) {
      return ResponseUtils::success([
        'redirect_url' => $url,
        'redirect_required' => true,
      ]);
    }

    // Nếu là request từ trình duyệt, trả về HTML với script chuyển hướng
    return response($this->vnpayService->createRedirectScript($url))
      ->header('Content-Type', 'text/html');
  }
}
