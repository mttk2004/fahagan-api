// Base types
export type BaseResponse<T = any> = {
  status: number;
  message: string;
  data?: T;
  errors?: Record<string, string[]>;
};

export type BaseCollectionResponse<T> = {
  data: T[];
  included?: any[];
};

export type BaseResource<T> = {
  type: string;
  id: number;
  attributes: T;
  relationships?: Record<string, any>;
  links?: {
    self: string;
  };
};

// User types
export type UserAttributes = {
  first_name: string;
  last_name: string;
  email: string;
  is_customer: boolean;
  full_name?: string;
  phone?: string;
  last_login?: string;
  created_at?: string;
  updated_at?: string;
  deleted_at?: string;
  roles?: string[];
  permissions?: string[];
};

export type UserResource = BaseResource<UserAttributes>;
export type UserCollection = BaseCollectionResponse<UserResource>;

// Book types
export type BookAttributes = {
  title: string;
  price: number;
  edition: string;
  image_url: string;
  publication_date: string;
  sold_count: number;
  description?: string;
  pages?: number;
  available_count?: number;
  created_at?: string;
  updated_at?: string;
  deleted_at?: string;
};

export type BookResource = BaseResource<BookAttributes>;
export type BookCollection = BaseCollectionResponse<BookResource>;

// Author types
export type AuthorAttributes = {
  name: string;
  biography?: string;
};

export type AuthorResource = BaseResource<AuthorAttributes>;
export type AuthorCollection = BaseCollectionResponse<AuthorResource>;

// Publisher types
export type PublisherAttributes = {
  name: string;
  biography?: string;
};

export type PublisherResource = BaseResource<PublisherAttributes>;
export type PublisherCollection = BaseCollectionResponse<PublisherResource>;

// Genre types
export type GenreAttributes = {
  name: string;
  books_count: number;
  description?: string;
};

export type GenreResource = BaseResource<GenreAttributes>;
export type GenreCollection = BaseCollectionResponse<GenreResource>;

// Discount types
export type DiscountAttributes = {
  name: string;
  discount_type: string;
  discount_value: number;
  start_date: string;
  end_date: string;
  created_at?: string;
  updated_at?: string;
  deleted_at?: string;
};

export type DiscountResource = BaseResource<DiscountAttributes>;
export type DiscountCollection = BaseCollectionResponse<DiscountResource>;

// Supplier types
export type SupplierAttributes = {
  name: string;
  phone: string;
  email: string;
  books_count: number;
  city?: string;
  ward?: string;
  address_line?: string;
  created_at?: string;
  updated_at?: string;
};

export type SupplierResource = BaseResource<SupplierAttributes>;
export type SupplierCollection = BaseCollectionResponse<SupplierResource>;

// Address types
export type AddressAttributes = {
  name: string;
  phone: string;
  city: string;
  district: string;
  ward: string;
  address_line: string;
};

export type AddressResource = BaseResource<AddressAttributes>;
export type AddressCollection = BaseCollectionResponse<AddressResource>;

// Cart types
export type CartItemAttributes = {
  quantity: number;
};

export type CartItemResource = BaseResource<CartItemAttributes>;
export type CartItemCollection = BaseCollectionResponse<CartItemResource>;

// API Response types
export type ApiResponse<T> = BaseResponse<T>;
export type ApiCollectionResponse<T> = BaseResponse<BaseCollectionResponse<T>>;
export type ApiResourceResponse<T> = BaseResponse<BaseResource<T>>;
