// Định nghĩa kiểu cơ bản cho API Response
export interface ApiResponse<T> {
  status?: number;
  message?: string;
  data: T;
  links?: PaginationLinks;
  meta?: PaginationMeta;
}

// Định nghĩa kiểu cho phân trang
export interface PaginationLinks {
  first: string;
  last: string;
  prev: string | null;
  next: string | null;
}

export interface PaginationMetaLink {
  url: string | null;
  label: string;
  active: boolean;
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

// Định nghĩa kiểu cơ bản cho Resource
export interface Resource {
  type: string;
  id: number | string | bigint;
  attributes: Record<string, any>;
  relationships?: Record<string, any>;
}

// Định nghĩa các kiểu cụ thể cho từng loại resource
export interface BookResource extends Resource {
  type: 'book';
  id: bigint;
  attributes: {
    title: string;
    price: string;
    edition: number;
    image_url: string | null;
    publication_date: string;
    sold_count: number;
    available_count: number;
    discount_value: number;
    description: string | null;
    pages: number;
    created_at: string;
    updated_at: string | null;
    deleted_at: string | null;
  };
  relationships?: {
    authors?: { data: AuthorResource[] };
    genres?: { data: GenreResource[] };
    publisher?: PublisherResource;
  };
}

export interface AuthorResource extends Resource {
  type: 'author';
  id: number;
  attributes: {
    name: string;
    image_url: string | null;
    biography: string | null;
  };
  relationships?: {
    books?: { data: BookResource[] };
  };
}

export interface GenreResource extends Resource {
  type: 'genre';
  id: number;
  attributes: {
    name: string;
    slug: string;
    books_count: number;
    description: string | null;
  };
  relationships?: {
    books?: { data: BookResource[] };
  };
}

export interface PublisherResource extends Resource {
  type: 'publisher';
  id: number;
  attributes: {
    name: string;
    biography: string | null;
  };
  relationships?: {
    books?: { data: BookResource[] };
  };
}

export interface UserResource extends Resource {
  type: 'user';
  id: bigint;
  attributes: {
    first_name: string;
    last_name: string;
    email: string;
    is_customer: boolean;
    phone: string | null;
    full_name: string;
    last_login: string | null;
    created_at: string;
    updated_at: string | null;
    deleted_at: string | null;
    roles?: string[];
    permissions?: string[];
  };
  relationships?: {
    cart_items?: { data: CartItemResource[] };
  };
}

export interface CartItemResource extends Resource {
  type: 'cart_item';
  id: number;
  attributes: {
    quantity: number;
  };
  relationships?: {
    book?: BookResource;
  };
}

export interface OrderResource extends Resource {
  type: 'order';
  id: bigint;
  attributes: {
    status: string;
    shopping_name: string;
    shopping_phone: string;
    shopping_city: string;
    shopping_district: string;
    shopping_ward: string;
    shopping_address_line: string;
    ordered_at: string;
    approved_at: string | null;
    delivering_at: string | null;
    delivered_at: string | null;
    completed_at: string | null;
    canceled_at: string | null;
    created_at: string;
    updated_at: string | null;
  };
  relationships?: {
    items?: { data: OrderItemResource[] };
    customer?: UserResource;
    employee?: UserResource;
    payment?: PaymentResource;
  };
}

export interface OrderItemResource extends Resource {
  type: 'order_item';
  id: number;
  attributes: {
    quantity: number;
    price_at_time: number;
    discount_value: number;
  };
  relationships?: {
    book?: BookResource;
  };
}

export interface DiscountResource extends Resource {
  type: 'discount';
  id: bigint;
  attributes: {
    name: string;
    discount_type: 'percentage' | 'fixed';
    discount_value: number;
    target_type: 'book' | 'order';
    min_purchase_amount: number | null;
    max_discount_amount: number | null;
    start_date: string;
    end_date: string;
    is_active: boolean;
    description: string | null;
    created_at: string;
    updated_at: string;
  };
  relationships?: {
    targets?: { data: BookResource[] };
  };
}

export interface AddressResource extends Resource {
  type: 'address';
  id: number;
  attributes: {
    name: string;
    phone: string;
    city: string;
    district: string;
    ward: string;
    address_line: string;
  };
}

export interface StockImportResource extends Resource {
  type: 'stock_import';
  id: bigint;
  attributes: {
    original_total_cost: number;
    discount_value: number;
    imported_at: string;
    created_at: string;
    updated_at: string;
  };
  relationships?: {
    employee?: UserResource;
    supplier?: SupplierResource;
    items?: { data: StockImportItemResource[] };
  };
}

export interface StockImportItemResource extends Resource {
  type: 'stock_import_item';
  id: number;
  attributes: {
    quantity: number;
    unit_price: number;
    sub_total: number;
  };
  relationships?: {
    book?: BookResource;
  };
}

export interface SupplierResource extends Resource {
  type: 'supplier';
  id: number;
  attributes: {
    name: string;
    phone: string | null;
    email: string | null;
    books_count: number;
    city: string;
    district: string;
    ward: string;
    address_line: string;
    created_at: string;
    updated_at: string;
  };
  relationships?: {
    books?: { data: BookResource[] };
  };
}

export interface PaymentResource extends Resource {
  type: 'payment';
  id: bigint;
  attributes: {
    status: string;
    method: string;
    total_amount: number;
    discount_value: number;
    transaction_ref: string | null;
    gateway_response: string | null;
    created_at: string;
    updated_at: string;
  };
}

export interface RoleResource extends Resource {
  type: 'role';
  id: number;
  attributes: {
    name: string;
    permissions: string[];
    created_at: string;
    updated_at: string;
  };
}

// Định nghĩa các kiểu phản hồi API
export type BookResponse = ApiResponse<BookResource>;
export type BookCollectionResponse = ApiResponse<BookResource[]>;

export type AuthorResponse = ApiResponse<AuthorResource>;
export type AuthorCollectionResponse = ApiResponse<AuthorResource[]>;

export type GenreResponse = ApiResponse<GenreResource>;
export type GenreCollectionResponse = ApiResponse<GenreResource[]>;

export type PublisherResponse = ApiResponse<PublisherResource>;
export type PublisherCollectionResponse = ApiResponse<PublisherResource[]>;

export type UserResponse = ApiResponse<UserResource>;
export type UserCollectionResponse = ApiResponse<UserResource[]>;

export type CartItemResponse = ApiResponse<CartItemResource>;
export type CartItemCollectionResponse = ApiResponse<CartItemResource[]>;

export type OrderResponse = ApiResponse<OrderResource>;
export type OrderCollectionResponse = ApiResponse<OrderResource[]>;

export type OrderItemResponse = ApiResponse<OrderItemResource>;
export type OrderItemCollectionResponse = ApiResponse<OrderItemResource[]>;

export type DiscountResponse = ApiResponse<DiscountResource>;
export type DiscountCollectionResponse = ApiResponse<DiscountResource[]>;

export type AddressResponse = ApiResponse<AddressResource>;
export type AddressCollectionResponse = ApiResponse<AddressResource[]>;

export type StockImportResponse = ApiResponse<StockImportResource>;
export type StockImportCollectionResponse = ApiResponse<StockImportResource[]>;

export type StockImportItemResponse = ApiResponse<StockImportItemResource>;
export type StockImportItemCollectionResponse = ApiResponse<StockImportItemResource[]>;

export type SupplierResponse = ApiResponse<SupplierResource>;
export type SupplierCollectionResponse = ApiResponse<SupplierResource[]>;

export type PaymentResponse = ApiResponse<PaymentResource>;
export type PaymentCollectionResponse = ApiResponse<PaymentResource[]>;

export type RoleResponse = ApiResponse<RoleResource>;
export type RoleCollectionResponse = ApiResponse<RoleResource[]>;

/**
 * # Hướng dẫn sử dụng hệ thống kiểu dữ liệu
 *
 * Hệ thống kiểu này được thiết kế để phản ánh chính xác cấu trúc JSON:API từ backend Laravel.
 *
 * ## Cấu trúc cơ bản
 *
 * ### ApiResponse<T>
 * Đại diện cho cấu trúc phản hồi API từ server, bao gồm:
 * - status: Mã trạng thái HTTP
 * - message: Thông báo từ server
 * - data: Dữ liệu trả về (có thể là một resource hoặc một mảng resource)
 * - links: Liên kết phân trang (nếu có)
 * - meta: Thông tin phân trang (nếu có)
 *
 * ### Resource
 * Đại diện cho một tài nguyên trong hệ thống, tuân theo cấu trúc JSON:API:
 * - type: Loại tài nguyên (book, author, genre, ...)
 * - id: ID của tài nguyên
 * - attributes: Các thuộc tính của tài nguyên
 * - relationships: Mối quan hệ với các tài nguyên khác (nếu có)
 *
 * ## Cách sử dụng
 *
 * ### Ví dụ 1: Lấy danh sách sách
 * ```typescript
 * import { BookCollectionResponse } from './types';
 *
 * const fetchBooks = async (): Promise<BookCollectionResponse> => {
 *   const response = await fetch('/api/v1/books');
 *   return response.json();
 * };
 *
 * // Sử dụng
 * const books = await fetchBooks();
 * console.log(books.status); // 200
 * console.log(books.message); // "Tải danh sách sách thành công."
 * console.log(books.data[0].attributes.title); // Tiêu đề sách đầu tiên
 *
 * // Xử lý phân trang
 * if (books.meta) {
 *   console.log(`Trang hiện tại: ${books.meta.current_page}`);
 *   console.log(`Tổng số trang: ${books.meta.last_page}`);
 *   console.log(`Tổng số sách: ${books.meta.total}`);
 * }
 * ```
 *
 * ### Ví dụ 2: Lấy chi tiết sách
 * ```typescript
 * import { BookResponse } from './types';
 *
 * const fetchBook = async (id: number): Promise<BookResponse> => {
 *   const response = await fetch(`/api/v1/books/${id}`);
 *   return response.json();
 * };
 *
 * // Sử dụng
 * const book = await fetchBook(1);
 * console.log(book.data.attributes.title); // Tiêu đề sách
 *
 * // Truy cập mối quan hệ
 * if (book.data.relationships?.authors) {
 *   console.log(book.data.relationships.authors.data.map(author => author.attributes.name));
 * }
 * ```
 *
 * ### Ví dụ 3: Tạo một sách mới
 * ```typescript
 * import { BookResponse, BookResource } from './types';
 *
 * const createBook = async (bookData: Partial<BookResource['attributes']>): Promise<BookResponse> => {
 *   const response = await fetch('/api/v1/books', {
 *     method: 'POST',
 *     headers: {
 *       'Content-Type': 'application/json',
 *     },
 *     body: JSON.stringify({ data: { attributes: bookData } }),
 *   });
 *   return response.json();
 * };
 *
 * // Sử dụng
 * const newBook = await createBook({
 *   title: 'Sách mới',
 *   price: 100000,
 *   publication_date: '2023-01-01',
 *   // ...
 * });
 * ```
 *
 * ### Ví dụ 4: Cập nhật sách
 * ```typescript
 * import { BookResponse } from './types';
 *
 * const updateBook = async (id: number, bookData: Partial<BookResource['attributes']>): Promise<BookResponse> => {
 *   const response = await fetch(`/api/v1/books/${id}`, {
 *     method: 'PUT',
 *     headers: {
 *       'Content-Type': 'application/json',
 *     },
 *     body: JSON.stringify({ data: { attributes: bookData } }),
 *   });
 *   return response.json();
 * };
 *
 * // Sử dụng
 * const updatedBook = await updateBook(1, {
 *   price: 150000,
 * });
 * ```
 */
