<?php

namespace App\Enums\Discount;

enum DiscountValidationMessages
{
    case NAME_REQUIRED;
    case NAME_STRING;
    case NAME_MAX;
    case NAME_UNIQUE;

    case DISCOUNT_TYPE_REQUIRED;
    case DISCOUNT_TYPE_STRING;
    case DISCOUNT_TYPE_IN;

    case DISCOUNT_VALUE_REQUIRED;
    case DISCOUNT_VALUE_NUMERIC;
    case DISCOUNT_VALUE_MIN;

    case START_DATE_REQUIRED;
    case START_DATE_DATE;
    case START_DATE_BEFORE_OR_EQUAL;

    case END_DATE_REQUIRED;
    case END_DATE_DATE;
    case END_DATE_AFTER_OR_EQUAL;

    public function message(): string
    {
        return match($this) {
            self::NAME_REQUIRED => 'Tên mã giảm giá là trường bắt buộc.',
            self::NAME_STRING => 'Tên mã giảm giá nên là một chuỗi.',
            self::NAME_MAX => 'Tên mã giảm giá nên có độ dài tối đa 255 ký tự.',
            self::NAME_UNIQUE => 'Tên mã giảm giá đã tồn tại.',

            self::DISCOUNT_TYPE_REQUIRED => 'Loại giảm giá là trường bắt buộc.',
            self::DISCOUNT_TYPE_STRING => 'Loại giảm giá nên là một chuỗi.',
            self::DISCOUNT_TYPE_IN => 'Loại giảm giá chỉ có thể là phần trăm hoặc cố định.',

            self::DISCOUNT_VALUE_REQUIRED => 'Giá trị giảm giá là trường bắt buộc.',
            self::DISCOUNT_VALUE_NUMERIC => 'Giá trị giảm giá nên là một số.',
            self::DISCOUNT_VALUE_MIN => 'Giá trị giảm giá nên có giá trị tối thiểu 0.',

            self::START_DATE_REQUIRED => 'Ngày bắt đầu là trường bắt buộc.',
            self::START_DATE_DATE => 'Ngày bắt đầu nên là một ngày.',
            self::START_DATE_BEFORE_OR_EQUAL => 'Ngày bắt đầu nên trước hoặc bằng ngày kết thúc.',

            self::END_DATE_REQUIRED => 'Ngày kết thúc là trường bắt buộc.',
            self::END_DATE_DATE => 'Ngày kết thúc nên là một ngày.',
            self::END_DATE_AFTER_OR_EQUAL => 'Ngày kết thúc nên sau hoặc bằng ngày bắt đầu.',
        };
    }
}
