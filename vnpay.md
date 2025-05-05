Dưới đây là một file README giải thích về quy trình thanh toán VNPay trong ứng dụng của bạn:

# Tích hợp thanh toán VNPay

## Giới thiệu

Tài liệu này mô tả quy trình thanh toán VNPay trong hệ thống và cung cấp hướng dẫn cho frontend để tích hợp.

VNPay là một cổng thanh toán trực tuyến phổ biến tại Việt Nam, cho phép người dùng thanh toán thông qua nhiều phương thức khác nhau như thẻ nội địa, thẻ quốc tế, QR code, và ví điện tử.

## Quy trình thanh toán

### Tổng quan quy trình

1. Khách hàng tạo đơn hàng và chọn phương thức thanh toán VNPay
2. Hệ thống tạo đơn hàng với trạng thái payment là PENDING
3. Hệ thống tạo URL thanh toán VNPay và trả về cho frontend
4. Frontend chuyển hướng khách hàng đến trang thanh toán VNPay
5. Khách hàng hoàn tất thanh toán trên cổng VNPay
6. VNPay gửi kết quả thanh toán về hệ thống qua returnUrl
7. Hệ thống xác thực và cập nhật trạng thái đơn hàng

### Chi tiết luồng dữ liệu

```
┌────────────┐      ┌────────────┐      ┌────────────┐      ┌────────────┐
│            │      │            │      │            │      │            │
│  Frontend  │──1──▶│  Backend   │──2──▶│   VNPay    │◀─3──▶│   Khách    │
│            │◀─5──▶│            │◀─4──▶│            │      │   hàng     │
│            │      │            │      │            │      │            │
└────────────┘      └────────────┘      └────────────┘      └────────────┘
```

1. Frontend gọi API tạo đơn hàng với method=vnpay
2. Backend tạo URL thanh toán VNPay và trả về
3. Khách hàng được chuyển hướng đến trang thanh toán VNPay
4. VNPay gửi kết quả về backend qua returnUrl
5. Frontend có thể kiểm tra trạng thái đơn hàng sau khi thanh toán

## Cấu hình

### Cấu hình môi trường (.env)

```
VNP_TMNCODE=VAAJN51S
VNP_HASHSECRET=UNOBMR165GLWAXUC51RO1I89FWIBH6V8
VNP_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
VNP_RETURN_URL=http://your-domain.com/api/v1/payments/vnpay-return
```

### Cấu hình trong dự án

File `config/vnpay.php` đã được cấu hình với các tham số cần thiết:

```php
return [
  'tmnCode' => env('VNP_TMNCODE', 'VAAJN51S'),
  'hashSecret' => env('VNP_HASHSECRET', 'UNOBMR165GLWAXUC51RO1I89FWIBH6V8'),
  'url' => env('VNP_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
  'returnUrl' => env('VNP_RETURN_URL', 'http://localhost:8000/api/v1/payments/vnpay-return'),
  'version' => '2.1.0',
  'command' => 'pay',
  'currCode' => 'VND',
  'locale' => 'vn',
];
```

## API Endpoints

### 1. Tạo đơn hàng với phương thức thanh toán VNPay

**Endpoint:** `POST /api/v1/customer/orders`

**Body:**
```json
{
  "data": {
    "attributes": {
      "method": "vnpay"
    },
    "relationships": {
      "address": {
        "id": "address_id"
      },
      "items": [
        {
          "id": "cart_item_id"
        }
      ]
    }
  }
}
```

**Response:**
```json
{
  "status": 201,
  "message": "Đơn hàng đã được tạo. Vui lòng thanh toán để hoàn tất.",
  "data": {
    "order": {
      "id": "order_id",
      "status": "pending",
      "total_amount": 100000,
      "created_at": "2025-05-03T12:34:56.000000Z"
    },
    "payment_url": "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html?...",
    "redirect_required": true
  }
}
```

### 2. Lấy URL thanh toán cho đơn hàng đã tạo

**Endpoint:** `GET /api/v1/customer/orders/{order_id}/pay-vnpay`

**Response:**
```json
{
  "status": 200,
  "message": "Đang chờ thanh toán.",
  "data": {
    "payment_url": "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html?...",
    "order_id": "order_id"
  }
}
```

### 3. Callback từ VNPay

**Endpoint:** `GET /api/v1/payments/vnpay-return`

Đây là URL mà VNPay sẽ gọi sau khi khách hàng hoàn tất thanh toán. Backend sẽ xử lý dữ liệu từ VNPay và trả về kết quả thành công hoặc thất bại.

## Hướng dẫn cho Frontend

### 1. Tạo đơn hàng

```javascript
// Giả sử dùng axios
async function createOrder(addressId, cartItemIds) {
  try {
    const response = await axios.post('/api/v1/customer/orders', {
      data: {
        attributes: {
          method: 'vnpay'
        },
        relationships: {
          address: {
            id: addressId
          },
          items: cartItemIds.map(id => ({ id }))
        }
      }
    });

    // Nếu thành công và cần chuyển hướng
    if (response.data.data.redirect_required) {
      // Lưu order_id vào localStorage hoặc state để kiểm tra sau này
      localStorage.setItem('current_order_id', response.data.data.order.id);

      // Chuyển hướng đến trang thanh toán VNPay
      window.location.href = response.data.data.payment_url;
    }

    return response.data;
  } catch (error) {
    console.error('Lỗi khi tạo đơn hàng:', error);
    throw error;
  }
}
```

### 2. Xử lý sau khi thanh toán

Sau khi khách hàng thanh toán xong tại VNPay, họ sẽ được chuyển hướng về trang của bạn (success page hoặc failure page). Bạn cần xử lý trường hợp này:

```javascript
// Trang success hoặc failure
document.addEventListener('DOMContentLoaded', async () => {
  // Lấy order_id đã lưu
  const orderId = localStorage.getItem('current_order_id');

  if (orderId) {
    try {
      // Kiểm tra trạng thái đơn hàng
      const response = await axios.get(`/api/v1/customer/orders/${orderId}`);
      const order = response.data.data.order;

      // Hiển thị kết quả dựa trên trạng thái
      if (order.payment && order.payment.status === 'paid') {
        // Hiển thị thông báo thanh toán thành công
        showSuccessMessage('Thanh toán thành công!');
      } else if (order.payment && order.payment.status === 'failed') {
        // Hiển thị thông báo thanh toán thất bại
        showErrorMessage('Thanh toán thất bại. Vui lòng thử lại!');
      }

      // Xóa order_id đã lưu
      localStorage.removeItem('current_order_id');
    } catch (error) {
      console.error('Lỗi khi kiểm tra trạng thái đơn hàng:', error);
    }
  }
});
```

### 3. Trang thanh toán thành công/thất bại

Bạn nên chuẩn bị các trang để hiển thị kết quả thanh toán:

1. **Trang thanh toán thành công**: Hiển thị thông tin đơn hàng, mã giao dịch, và cảm ơn khách hàng
2. **Trang thanh toán thất bại**: Hiển thị lý do thất bại và đề xuất các phương thức thanh toán khác

## Môi trường test

VNPay cung cấp môi trường sandbox để kiểm thử:

- URL: https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
- Tài khoản ngân hàng test: NCB với thông tin:
  - Số thẻ: 9704198526191432198
  - Tên chủ thẻ: NGUYEN VAN A
  - Ngày phát hành: 07/15
  - Mật khẩu OTP: 123456

## Lưu ý quan trọng

1. **Bảo mật**: Không lưu thông tin thanh toán nhạy cảm của khách hàng
2. **Xác thực**: Luôn kiểm tra chữ ký từ VNPay để đảm bảo dữ liệu không bị giả mạo
3. **Xử lý lỗi**: Chuẩn bị các trường hợp xử lý lỗi khi giao dịch thất bại
4. **Chuyển đổi tiền tệ**: VNPay yêu cầu số tiền được chuyển đổi sang đơn vị nhỏ nhất (VNĐ * 100)
5. **Kiểm tra trùng lặp**: Tránh xử lý trùng lặp các giao dịch bằng cách kiểm tra transaction_ref

## Tài liệu tham khảo

- [Tài liệu chính thức của VNPay](https://sandbox.vnpayment.vn/apis/docs/gioi-thieu/)
- [Ví dụ tích hợp VNPay trên GitHub](https://github.com/vinhkosd/example-vnpay-laravel)

---

Nếu bạn có bất kỳ câu hỏi hoặc gặp vấn đề khi tích hợp, vui lòng liên hệ với đội phát triển.
