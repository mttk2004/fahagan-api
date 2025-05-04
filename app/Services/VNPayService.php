<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\URL;

class VNPayService
{
    /**
     * Tạo URL thanh toán VNPay
     *
     * @param Order $order
     * @param Payment $payment
     * @return string URL thanh toán
     */
    public function createPaymentUrl(Order $order, Payment $payment): string
    {
        $vnp_Url = config('vnpay.url');
        $vnp_ReturnUrl = config('vnpay.returnUrl');
        $vnp_TmnCode = config('vnpay.tmnCode');
        $vnp_HashSecret = config('vnpay.hashSecret');
        $vnp_Version = config('vnpay.version');
        $vnp_Command = config('vnpay.command');
        $vnp_CurrCode = config('vnpay.currCode');
        $vnp_Locale = config('vnpay.locale');

        // Thông tin đơn hàng
        // Format số tiền thành số nguyên * 100 (VD: 10.000đ -> 1000000)
        $vnp_Amount = $payment->total_amount * 100;
        $vnp_OrderInfo = 'Thanh toan don hang #' . $order->id;
        $vnp_OrderType = 'billpayment';
        $vnp_TxnRef = $order->id . '-' . time(); // Mã đơn hàng (order_id + timestamp)

        // Lưu transaction reference vào payment
        $payment->transaction_ref = $vnp_TxnRef;
        $payment->save();

        $inputData = [
          "vnp_Version" => $vnp_Version,
          "vnp_TmnCode" => $vnp_TmnCode,
          "vnp_Amount" => $vnp_Amount,
          "vnp_Command" => $vnp_Command,
          "vnp_CreateDate" => date('YmdHis'),
          "vnp_CurrCode" => $vnp_CurrCode,
          "vnp_IpAddr" => request()->ip(),
          "vnp_Locale" => $vnp_Locale,
          "vnp_OrderInfo" => $vnp_OrderInfo,
          "vnp_OrderType" => $vnp_OrderType,
          "vnp_ReturnUrl" => $vnp_ReturnUrl,
          "vnp_TxnRef" => $vnp_TxnRef,
        ];

        // Sắp xếp theo key
        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";

        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        // Tạo URL thông qua các thông tin đã gửi
        $vnp_Url = $vnp_Url . "?" . $query;

        // Tạo chữ ký
        $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
        $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;

        return $vnp_Url;
    }

    /**
     * Tạo chuỗi JavaScript để thực hiện chuyển hướng trình duyệt từ frontend
     *
     * @param string $url URL cần chuyển hướng đến
     * @return string Chuỗi JavaScript thực hiện chuyển hướng
     */
    public function createRedirectScript(string $url): string
    {
        return "<script>window.location.href = '$url';</script>";
    }

    /**
     * Xử lý response từ VNPay sau khi thanh toán
     *
     * @param array $data
     * @return array
     */
    public function processReturnUrl(array $data): array
    {
        $vnp_HashSecret = config('vnpay.hashSecret');
        $vnp_SecureHash = $data['vnp_SecureHash'] ?? '';

        // Xóa vnp_SecureHash từ data để tính toán và so sánh hash
        unset($data['vnp_SecureHash']);

        // Sắp xếp dữ liệu theo key
        ksort($data);

        // Tạo chuỗi hash
        $i = 0;
        $hashData = "";
        foreach ($data as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        // Tính toán secure hash
        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        // Kiểm tra chữ ký
        $isValidSignature = ($secureHash === $vnp_SecureHash);

        // Trích xuất order ID từ vnp_TxnRef (có định dạng "order_id-timestamp")
        $txnRef = $data['vnp_TxnRef'] ?? '';
        $orderId = explode('-', $txnRef)[0] ?? null;

        // Kiểm tra kết quả thanh toán
        $responseCode = $data['vnp_ResponseCode'] ?? '';
        $transactionStatus = $data['vnp_TransactionStatus'] ?? '';
        $paymentStatus = PaymentStatus::FAILED;

        // Thanh toán thành công khi mã phản hồi = 00
        if ($isValidSignature && $responseCode == '00' && $transactionStatus == '00') {
            $paymentStatus = PaymentStatus::PAID;
        }

        return [
          'isValidSignature' => $isValidSignature,
          'paymentStatus' => $paymentStatus,
          'orderId' => $orderId,
          'txnRef' => $txnRef,
          'responseCode' => $responseCode,
          'amount' => ($data['vnp_Amount'] ?? 0) / 100, // Chuyển về đơn vị VNĐ
          'rawData' => $data,
        ];
    }
}
