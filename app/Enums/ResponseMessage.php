<?php

namespace App\Enums;


enum ResponseMessage: string
{
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

	/**
	 * @group Create
	 */
	case CREATED_BOOK = 'Sách đã được tạo thành công.';
	case CREATED_GENRE = 'Thể loại đã được tạo thành công.';
	case CREATED_PUBLISHER = 'Nhà xuất bản đã được tạo thành công.';
	case CREATED_USER = 'Người dùng đã được tạo thành công.';
	case CREATED_AUTHOR = 'Tác giả đã được tạo thành công.';
	case CREATED_DISCOUNT = 'Giảm giá đã được tạo thành công.';
	case CREATED_ADDRESS = 'Địa chỉ đã được tạo thành công.';
	case CREATED_SUPPLIER = 'Nhà cung cấp đã được tạo thành công.';

	/**
	 * @group Update
	 */
	case UPDATED_BOOK = 'Sách đã được cập nhật thành công.';
	case UPDATED_GENRE = 'Thể loại đã được cập nhật thành công.';
	case UPDATED_PUBLISHER = 'Nhà xuất bản đã được cập nhật thành công.';
	case UPDATED_USER = 'Người dùng đã được cập nhật thành công.';
	case UPDATED_AUTHOR = 'Tác giả đã được cập nhật thành công.';
	case UPDATED_DISCOUNT = 'Giảm giá đã được cập nhật thành công.';
	case UPDATED_ADDRESS = 'Địa chỉ đã được cập nhật thành công.';
	case UPDATED_SUPPLIER = 'Nhà cung cấp đã được cập nhật thành công.';

	/**
	 * @group Delete
	 */
	case DELETED_BOOK = 'Sách đã được xóa thành công.';
	case DELETED_GENRE = 'Thể loại đã được xóa thành công.';
	case DELETED_PUBLISHER = 'Nhà xuất bản đã được xóa thành công.';
	case DELETED_USER = 'Người dùng đã được xóa thành công.';
	case DELETED_AUTHOR = 'Tác giả đã được xóa thành công.';
	case DELETED_DISCOUNT = 'Giảm giá đã được xóa thành công.';
	case DELETED_ADDRESS = 'Địa chỉ đã được xóa thành công.';
	case DELETED_SUPPLIER = 'Nhà cung cấp đã được xóa thành công.';

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
	case ADDED_TO_CART = 'Sách đã được thêm vào giỏ hàng.';
	case REMOVED_FROM_CART = 'Sách đã được xóa khỏi giỏ hàng.';
	case UPDATED_CART_ITEM = 'Số lượng sách trong giỏ hàng đã được cập nhật.';
	case NOT_FOUND_CART_ITEM = 'Sách không tồn tại trong giỏ hàng.';
	case ALREADY_IN_CART = 'Sách đã tồn tại trong giỏ hàng.';
}
