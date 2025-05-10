# Hướng dẫn sử dụng hệ thống kiểu dữ liệu TypeScript

Hệ thống kiểu dữ liệu này được thiết kế để phản ánh chính xác cấu trúc JSON:API từ backend Laravel, giúp việc tương tác với API trở nên dễ dàng và an toàn hơn với TypeScript.

## Cấu trúc cơ bản

### ApiResponse<T>

Đại diện cho cấu trúc phản hồi API từ server, bao gồm:

```typescript
export interface ApiResponse<T> {
  status: number;        // Mã trạng thái HTTP
  message: string;       // Thông báo từ server
  data: T;               // Dữ liệu trả về (có thể là một resource hoặc một mảng resource)
  links?: PaginationLinks; // Liên kết phân trang (nếu có)
  meta?: PaginationMeta;   // Thông tin phân trang (nếu có)
}
```

### Resource

Đại diện cho một tài nguyên trong hệ thống, tuân theo cấu trúc JSON:API:

```typescript
export interface Resource {
  type: string;                   // Loại tài nguyên (book, author, genre, ...)
  id: number | string;            // ID của tài nguyên
  attributes: Record<string, any>; // Các thuộc tính của tài nguyên
  relationships?: Record<string, any>; // Mối quan hệ với các tài nguyên khác (nếu có)
}
```

### Phân trang

Thông tin phân trang được định nghĩa trong các interface sau:

```typescript
export interface PaginationLinks {
  first: string;
  last: string;
  prev: string | null;
  next: string | null;
}

export interface PaginationMeta {
  current_page: number;
  from: number | null;
  last_page: number;
  links: PaginationMetaLink[];
  path: string;
  per_page: number;
  to: number | null;
  total: number;
}

export interface PaginationMetaLink {
  url: string | null;
  label: string;
  active: boolean;
}
```

## Các kiểu tài nguyên

Hệ thống định nghĩa các kiểu tài nguyên cụ thể như:

- `BookResource`
- `AuthorResource`
- `GenreResource`
- `PublisherResource`
- `UserResource`
- `CartItemResource`
- `OrderResource`
- `OrderItemResource`
- `DiscountResource`
- `AddressResource`
- `StockImportResource`
- `StockImportItemResource`
- `SupplierResource`
- `PaymentResource`
- `RoleResource`

Mỗi tài nguyên đều có các kiểu phản hồi tương ứng:

- `XxxResponse`: Phản hồi cho một tài nguyên đơn lẻ
- `XxxCollectionResponse`: Phản hồi cho một tập hợp tài nguyên

## Cách sử dụng

### Ví dụ 1: Lấy danh sách sách

```typescript
import { BookCollectionResponse } from '../types';

const fetchBooks = async (): Promise<BookCollectionResponse> => {
  const response = await fetch('/api/v1/books');
  return response.json();
};

// Sử dụng
const books = await fetchBooks();
console.log(books.status); // 200
console.log(books.message); // "Tải danh sách sách thành công."
console.log(books.data[0].attributes.title); // Tiêu đề sách đầu tiên

// Xử lý phân trang
if (books.meta) {
  console.log(`Trang hiện tại: ${books.meta.current_page}`);
  console.log(`Tổng số trang: ${books.meta.last_page}`);
  console.log(`Tổng số sách: ${books.meta.total}`);
}

// Xử lý liên kết phân trang
if (books.links?.next) {
  // Tải trang tiếp theo
  const nextPageUrl = books.links.next;
  const nextPageResponse = await fetch(nextPageUrl);
  const nextPageBooks = await nextPageResponse.json();
}
```

### Ví dụ 2: Lấy chi tiết sách

```typescript
import { BookResponse } from '../types';

const fetchBook = async (id: number): Promise<BookResponse> => {
  const response = await fetch(`/api/v1/books/${id}`);
  return response.json();
};

// Sử dụng
const book = await fetchBook(1);
console.log(book.data.attributes.title); // Tiêu đề sách

// Truy cập mối quan hệ
if (book.data.relationships?.authors) {
  console.log(book.data.relationships.authors.data.map(author => author.attributes.name));
}
```

### Ví dụ 3: Tạo một sách mới

```typescript
import { BookResponse, BookResource } from '../types';

const createBook = async (bookData: Partial<BookResource['attributes']>): Promise<BookResponse> => {
  const response = await fetch('/api/v1/books', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`,
    },
    body: JSON.stringify({ data: { attributes: bookData } }),
  });
  return response.json();
};

// Sử dụng
const newBook = await createBook({
  title: 'Sách mới',
  price: 100000,
  publication_date: '2023-01-01',
  // ...
});
```

### Ví dụ 4: Cập nhật sách

```typescript
import { BookResponse } from '../types';

const updateBook = async (id: number, bookData: Partial<BookResource['attributes']>): Promise<BookResponse> => {
  const response = await fetch(`/api/v1/books/${id}`, {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`,
    },
    body: JSON.stringify({ data: { attributes: bookData } }),
  });
  return response.json();
};

// Sử dụng
const updatedBook = await updateBook(1, {
  price: 150000,
});
```

### Ví dụ 5: Xử lý mối quan hệ

```typescript
import { BookResponse, AuthorResource } from '../types';

const updateBookAuthors = async (
  bookId: number,
  authorIds: number[]
): Promise<BookResponse> => {
  const response = await fetch(`/api/v1/books/${bookId}`, {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`,
    },
    body: JSON.stringify({
      data: {
        relationships: {
          authors: { data: authorIds.map(id => ({ type: 'author', id })) }
        }
      }
    }),
  });
  return response.json();
};

// Sử dụng
const updatedBook = await updateBookAuthors(1, [2, 3, 4]);
```

### Ví dụ 6: Xử lý lỗi

```typescript
import { BookResponse } from '../types';

const fetchBook = async (id: number): Promise<BookResponse> => {
  try {
    const response = await fetch(`/api/v1/books/${id}`);
    const data = await response.json();

    if (response.ok) {
      return data;
    } else {
      // Xử lý lỗi từ API
      console.error(`Lỗi: ${data.message}`);
      throw new Error(data.message);
    }
  } catch (error) {
    console.error('Lỗi khi gọi API:', error);
    throw error;
  }
};
```

## Lưu ý khi sử dụng

1. **Kiểm tra null và undefined**: Luôn kiểm tra sự tồn tại của các thuộc tính không bắt buộc trước khi truy cập.

2. **Eager loading**: Backend Laravel sử dụng `whenLoaded` để chỉ trả về mối quan hệ khi được eager loaded. Đảm bảo bạn yêu cầu eager loading khi cần thiết.

3. **Phân trang**: Khi làm việc với danh sách lớn, hãy sử dụng thông tin phân trang trong `meta` và `links` để điều hướng giữa các trang.

4. **Xử lý lỗi**: Luôn kiểm tra `status` trong phản hồi để xác định xem yêu cầu có thành công hay không.

5. **TypeScript Strict Mode**: Nên bật strict mode trong TypeScript để tận dụng tối đa hệ thống kiểu này.

## Các kiểu dữ liệu phổ biến

### Kiểu dữ liệu của thuộc tính

- `string`: Chuỗi văn bản
- `number`: Số
- `boolean`: Giá trị boolean (true/false)
- `string | null`: Chuỗi văn bản hoặc null
- `number | null`: Số hoặc null
- `Date`: Ngày tháng (được biểu diễn dưới dạng chuỗi ISO trong JSON)

### Kiểu dữ liệu của mối quan hệ

- `{ data: XxxResource[] }`: Mối quan hệ một-nhiều
- `XxxResource`: Mối quan hệ một-một

## Mở rộng hệ thống kiểu

Khi cần thêm một tài nguyên mới, hãy làm theo các bước sau:

1. Định nghĩa interface cho tài nguyên mới, kế thừa từ `Resource`
2. Định nghĩa các kiểu phản hồi cho tài nguyên mới
3. Cập nhật tài liệu này để bao gồm tài nguyên mới

## Kết luận

Hệ thống kiểu dữ liệu này giúp bạn tương tác với API một cách an toàn và dễ dàng hơn. Nó cung cấp gợi ý kiểu và kiểm tra kiểu tĩnh, giúp phát hiện lỗi sớm trong quá trình phát triển.
