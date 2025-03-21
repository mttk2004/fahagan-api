<?php

namespace App\Enums;


enum ResponseMessage: string
{
	case NOT_FOUND_BOOK = 'Không tìm thấy sách.';
	case NOT_FOUND_GENRE = 'Không tìm thấy thể loại.';
	case NOT_FOUND_PUBLISHER = 'Không tìm thấy nhà xuất bản.';
	case NOT_FOUND_USER = 'Không tìm thấy người dùng.';
	case NOT_FOUND_AUTHOR = 'Không tìm thấy tác giả.';
	case NOT_FOUND_DISCOUNT = 'Không tìm thấy giảm giá.';

	case CREATED_BOOK = 'Sách đã được tạo thành công.';
	case CREATED_GENRE = 'Thể loại đã được tạo thành công.';
	case CREATED_PUBLISHER = 'Nhà xuất bản đã được tạo thành công.';
	case CREATED_USER = 'Người dùng đã được tạo thành công.';
	case CREATED_AUTHOR = 'Tác giả đã được tạo thành công.';
	case CREATED_DISCOUNT = 'Giảm giá đã được tạo thành công.';

	case UPDATED_BOOK = 'Sách đã được cập nhật thành công.';
	case UPDATED_GENRE = 'Thể loại đã được cập nhật thành công.';
	case UPDATED_PUBLISHER = 'Nhà xuất bản đã được cập nhật thành công.';
	case UPDATED_USER = 'Người dùng đã được cập nhật thành công.';
	case UPDATED_AUTHOR = 'Tác giả đã được cập nhật thành công.';
	case UPDATED_DISCOUNT = 'Giảm giá đã được cập nhật thành công.';

	case DELETED_BOOK = 'Sách đã được xóa thành công.';
	case DELETED_GENRE = 'Thể loại đã được xóa thành công.';
	case DELETED_PUBLISHER = 'Nhà xuất bản đã được xóa thành công.';
	case DELETED_USER = 'Người dùng đã được xóa thành công.';
	case DELETED_AUTHOR = 'Tác giả đã được xóa thành công.';
	case DELETED_DISCOUNT = 'Giảm giá đã được xóa thành công.';
}
