<?php

namespace App\Enums;

enum ResponseMessage: string
{
/**
   * @group Success
   */
  case LOAD_USERS_SUCCESS = 'Tải danh sách người dùng thành công.';
  case LOAD_ORDERS_SUCCESS = 'Tải danh sách đơn hàng thành công.';
  case LOAD_BOOKS_SUCCESS = 'Tải danh sách sách thành công.';
  case LOAD_GENRES_SUCCESS = 'Tải danh sách thể loại thành công.';
  case LOAD_PUBLISHERS_SUCCESS = 'Tải danh sách nhà xuất bản thành công.';
  case LOAD_AUTHORS_SUCCESS = 'Tải danh sách tác giả thành công.';
  case LOAD_DISCOUNTS_SUCCESS = 'Tải danh sách mã giảm giá thành công.';
  case LOAD_ADDRESSES_SUCCESS = 'Tải danh sách địa chỉ thành công.';
  case LOAD_SUPPLIERS_SUCCESS = 'Tải danh sách nhà cung cấp thành công.';
  case LOAD_CART_ITEMS_SUCCESS = 'Tải danh sách sản phẩm trong giỏ hàng thành công.';
  case LOAD_ORDER_ITEMS_SUCCESS = 'Tải danh sách sản phẩm trong đơn hàng thành công.';
  case LOAD_STOCK_IMPORTS_SUCCESS = 'Tải danh sách nhập kho thành công.';
  case LOAD_STOCK_IMPORT_ITEMS_SUCCESS = 'Tải danh sách sản phẩm trong nhập kho thành công.';
  case LOAD_ROLES_SUCCESS = 'Tải danh sách vai trò thành công.';

/**
   * @group Error
   */
  case NOT_FOUND_BOOK = 'Không tìm thấy sách.';
  case NOT_FOUND_GENRE = 'Không tìm thấy thể loại.';
  case NOT_FOUND_PUBLISHER = 'Không tìm thấy nhà xuất bản.';
  case NOT_FOUND_USER = 'Không tìm thấy người dùng.';
  case NOT_FOUND_AUTHOR = 'Không tìm thấy tác giả.';
  case NOT_FOUND_DISCOUNT = 'Không tìm thấy giảm giá.';
  case NOT_FOUND_TARGET_OBJECT = 'Đối tượng áp dụng không tồn tại.';
  case NOT_FOUND_ADDRESS = 'Không tìm thấy địa chỉ.';
  case NOT_FOUND_SUPPLIER = 'Không tìm thấy nhà cung cấp.';
  case NOT_FOUND_ORDER = 'Không tìm thấy đơn hàng.';
  case NOT_FOUND_PAYMENT = 'Không tìm thấy thông tin thanh toán.';

/**
   * @group Create
   */
  case CREATED_BOOK = 'Thêm sách thành công.';
  case CREATED_GENRE = 'Thêm thể loại thành công.';
  case CREATED_PUBLISHER = 'Thêm nhà xuất bản thành công.';
  case CREATED_USER = 'Thêm người dùng thành công.';
  case CREATED_AUTHOR = 'Thêm tác giả thành công.';
  case CREATED_DISCOUNT = 'Thêm mã giảm giá thành công.';
  case CREATED_ADDRESS = 'Thêm địa chỉ thành công.';
  case CREATED_SUPPLIER = 'Thêm nhà cung cấp thành công.';
  case CREATED_ORDER = 'Tạo đơn hàng thành công.';

/**
   * @group Update
   */
  case UPDATED_BOOK = 'Cập nhật sách thành công.';
  case UPDATED_GENRE = 'Cập nhật thể loại thành công.';
  case UPDATED_PUBLISHER = 'Cập nhật nhà xuất bản thành công.';
  case UPDATED_USER = 'Cập nhật người dùng thành công.';
  case UPDATED_AUTHOR = 'Cập nhật tác giả thành công.';
  case UPDATED_DISCOUNT = 'Cập nhật mã giảm giá thành công.';
  case UPDATED_ADDRESS = 'Cập nhật địa chỉ thành công.';
  case UPDATED_SUPPLIER = 'Cập nhật nhà cung cấp thành công.';
  case UPDATED_ORDER = 'Cập nhật đơn hàng thành công.';

/**
   * @group Delete
   */
  case DELETED_BOOK = 'Xóa sách thành công.';
  case DELETED_GENRE = 'Xóa thể loại thành công.';
  case DELETED_PUBLISHER = 'Xóa nhà xuất bản thành công.';
  case DELETED_USER = 'Xóa người dùng thành công.';
  case DELETED_AUTHOR = 'Xóa tác giả thành công.';
  case DELETED_DISCOUNT = 'Xóa mã giảm giá thành công.';
  case DELETED_ADDRESS = 'Xóa địa chỉ thành công.';
  case DELETED_SUPPLIER = 'Xóa nhà cung cấp thành công.';
  case DELETED_ORDER = 'Xóa đơn hàng thành công.';

/**
   * @group Auth
   */
  case LOGIN_SUCCESS = 'Đăng nhập thành công.';
  case LOGIN_FAILED = 'Đăng nhập thất bại. Vui lòng kiểm tra lại thông tin đăng nhập.';
  case LOGOUT_SUCCESS = 'Đăng xuất thành công.';
  case REGISTER_SUCCESS = 'Đăng ký thành công.';
  case REGISTER_FAILED = 'Đăng ký thất bại. Vui lòng thử lại sau.';
  case CHANGE_PASSWORD_SUCCESS = 'Đổi mật khẩu thành công.';
  case CHANGE_PASSWORD_FAIL = 'Đổi mật khẩu thất bại.';
  case WRONG_OLD_PASSWORD = 'Mật khẩu cũ không chính xác.';

/**
   * @group Cart
   */
  case ADDED_TO_CART = 'Thêm vào giỏ hàng thành công.';
  case REMOVED_FROM_CART = 'Xóa khỏi giỏ hàng thành công.';
  case UPDATED_CART_ITEM = 'Số lượng sách trong giỏ hàng đã được cập nhật.';
  case NOT_FOUND_CART_ITEM = 'Không tìm thấy sản phẩm trong giỏ hàng.';
  case ALREADY_IN_CART = 'Sách đã tồn tại trong giỏ hàng.';

/**
   * @group Payment
   */
  case PAYMENT_SUCCESS = 'Thanh toán thành công.';
  case PAYMENT_FAILED = 'Thanh toán thất bại.';
  case PAYMENT_PENDING = 'Đang chờ thanh toán.';
  case PAYMENT_CANCELED = 'Thanh toán đã bị hủy.';
  case PAYMENT_PROCESSING = 'Đang xử lý thanh toán.';
  case PAYMENT_REQUIRED = 'Yêu cầu thanh toán.';
  case INVALID_PAYMENT_METHOD = 'Phương thức thanh toán không hợp lệ.';
  case INVALID_PAYMENT_SIGNATURE = 'Chữ ký thanh toán không hợp lệ.';

  case RESTORED_GENRE = 'Khôi phục thể loại thành công.';

    // Supplier
  case RESTORED_SUPPLIER = 'Khôi phục nhà cung cấp thành công.';

/**
   * @group Stock Import
   */
  case STOCK_IMPORT_CREATED = 'Tạo phiếu nhập kho thành công.';

  case VALIDATION_ERROR = 'Dữ liệu không hợp lệ.';
  case UNAUTHORIZED = 'Bạn không có quyền truy cập.';

  case FORBIDDEN = 'Bạn không được phép thực hiện hành động này.';
  case WRONG_CREDENTIALS = 'Thông tin đăng nhập không chính xác.';
  case ACCOUNT_NOT_ACTIVE = 'Tài khoản chưa được kích hoạt.';
  case ACCOUNT_BANNED = 'Tài khoản đã bị khóa.';
  case EMAIL_NOT_FOUND = 'Email không tồn tại trong hệ thống.';
  case EMAIL_ALREADY_EXISTS = 'Email đã tồn tại trong hệ thống.';
  case EMAIL_SENT = 'Email đã được gửi.';
  case PASSWORD_RESET_LINK_SENT = 'Link đặt lại mật khẩu đã được gửi. Vui lòng kiểm tra email của bạn.';
  case PASSWORD_RESET_SUCCESS = 'Đặt lại mật khẩu thành công. Vui lòng đăng nhập lại.';
  case TOKEN_INVALID = 'Token không hợp lệ.';
  case TOKEN_EXPIRED = 'Token đã hết hạn.';
  case REGISTERED = 'Đăng ký thành công. Vui lòng đăng nhập.';
}
