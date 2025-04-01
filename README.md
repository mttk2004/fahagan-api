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

**Lưu ý** nếu đã có dữ liệu rồi thì chạy **`php artisan migrate:fresh`** thay cho `php artisan migrate`.

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

_Last updated: 31 March, 2025_
