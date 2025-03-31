# Fahagan API

## Cài đặt:

Chạy các lệnh sau:

```bash
  git clone git@github.com:mttk2004/fahagan-api.git
  cd fahagan-api/
  composer install
  cp .env.example .env
  php artisan key:generate
```

Sau đó sửa các cấu hình cần thiết trong .env

### Tạo cơ sở dữ liệu và chạy migration:

Chạy các lệnh sau:

```bash
  php artisan migrate
  php artisan db:seed
```

### Chạy server:

Chạy lệnh sau để chạy server:

```bash
  php artisan serve
```

### Tạo tài liệu:

```bash
  php artisan script:generate
```

Sau khi chạy lệnh trên, tài liệu sẽ được tạo trong thư mục `public/docs`, có thể mở bằng cách truy cập vào đường dẫn `http://localhost:8000/docs` (hoặc đường dẫn tương ứng với server của bạn).


_Last updated: 31 March, 2025_
