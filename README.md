# Fahagan API

## Cài đặt:

Chạy các lệnh sau để cài đặt ứng dụng:

```bash
  git clone git@github.com:mttk2004/fahagan-api.git
  cd fahagan-api/
  composer install
  cp .env.example .env
  php artisan key:generate
```

Sau đó sửa các cấu hình cần thiết trong .env

### Tạo dữ liệu giả:

Chạy các lệnh sau:

```bash
  php artisan migrate
  php artisan db:seed
```

**Lưu ý** nếu đã có dữ liệu rồi thì chạy **`php artisan db:fresh`** để có dữ liệu mới nhất.

### Chạy server:

Chạy lệnh sau để chạy server:

```bash
  php artisan serve
```

### Tạo tài liệu:

```bash
  php artisan scribe:generate
```

Sau khi chạy lệnh trên, tài liệu sẽ được tạo trong thư mục `public/docs`, có thể mở bằng cách truy cập vào đường dẫn `http://localhost:8000/docs` (hoặc đường dẫn tương ứng với server của bạn).


## Quy trình thanh toán VNPay

### Luồng xử lý thanh toán VNPay

1. **Khách hàng tạo đơn hàng với phương thức thanh toán VNPay**:
   - Hệ thống tạo order với status = PENDING
   - Payment được tạo với status = PENDING, method = 'vnpay'
   - URL thanh toán VNPay được trả về cho khách hàng

2. **Khách hàng thanh toán tại VNPay**:
   - Khách hàng được chuyển hướng đến cổng thanh toán VNPay
   - Sau khi thanh toán, VNPay gửi callback đến hệ thống

3. **Hệ thống xử lý callback từ VNPay**:
   - Kiểm tra tính hợp lệ của callback
   - Cập nhật payment.status thành PAID hoặc FAILED
   - Chuyển hướng người dùng đến trang frontend tương ứng (thành công/thất bại)
   - **Lưu ý**: Order.status vẫn giữ là PENDING (chưa được duyệt)

4. **Nhân viên duyệt đơn hàng**:
   - Xem danh sách đơn hàng đang ở trạng thái PENDING
   - Kiểm tra thông tin đơn hàng, bao gồm trạng thái thanh toán (payment.status)
   - Nếu payment.status = PAID, nhân viên có thể duyệt đơn (cập nhật order.status thành APPROVED)
   - Khi đơn được duyệt, hệ thống tự động ghi nhận employee_id và thời gian duyệt đơn

### Cấu hình URL chuyển hướng sau thanh toán

Sau khi thanh toán VNPay, người dùng sẽ được chuyển hướng về trang frontend. Các URL chuyển hướng được cấu hình trong `.env`:

```
VNP_CLIENT_SUCCESS_URL=http://localhost:5173/payments/payment-success
VNP_CLIENT_FAILED_URL=http://localhost:5173/payments/payments-failed
```

Hệ thống chuyển hướng người dùng với các thông tin sau:
- URL thành công: `{VNP_CLIENT_SUCCESS_URL}?orderId={order_id}`
- URL thất bại: `{VNP_CLIENT_FAILED_URL}?orderId={order_id}` hoặc `{VNP_CLIENT_FAILED_URL}?message={error_message}`

### Hướng dẫn cho nhân viên duyệt đơn hàng

1. Đăng nhập vào hệ thống với quyền nhân viên
2. Vào phần quản lý đơn hàng, lọc đơn hàng theo trạng thái PENDING
3. Kiểm tra các đơn hàng có phương thức thanh toán là VNPay
4. Chỉ duyệt đơn hàng khi thấy payment.status = PAID
5. Để duyệt đơn, sử dụng API:
   ```
   PATCH /api/v1/orders/{order_id}/status
   {
     "status": "approved"
   }
   ```
6. Sau khi duyệt đơn, hệ thống sẽ tự động cập nhật trạng thái đơn hàng và lưu thông tin nhân viên duyệt đơn

_Last updated: 4 May, 2025_
