<?php

namespace Tests\Feature\Api\V1\Discount;

use App\DTOs\Discount\DiscountDTO;
use PHPUnit\Framework\TestCase;

class DiscountDTOTest extends TestCase
{
    public function test_it_can_create_discount_dto_with_all_properties()
    {
        // Tạo DiscountDTO với tất cả thuộc tính
        $discountDTO = new DiscountDTO(
            'Giảm giá mùa hè',
            'percentage',
            10.5,
            'book',
            100000, // min_purchase_amount
            50000, // max_discount_amount
            '2023-06-01',
            '2023-08-31',
            'Mô tả giảm giá',
            true,
            [1, 2, 3]
        );

        // Kiểm tra các thuộc tính
        $this->assertEquals('Giảm giá mùa hè', $discountDTO->name);
        $this->assertEquals('percentage', $discountDTO->discount_type);
        $this->assertEquals(10.5, $discountDTO->discount_value);
        $this->assertEquals('book', $discountDTO->target_type);
        $this->assertEquals(100000, $discountDTO->min_purchase_amount);
        $this->assertEquals(50000, $discountDTO->max_discount_amount);
        $this->assertEquals('2023-06-01', $discountDTO->start_date);
        $this->assertEquals('2023-08-31', $discountDTO->end_date);
        $this->assertEquals('Mô tả giảm giá', $discountDTO->description);
        $this->assertTrue($discountDTO->is_active);
        $this->assertEquals([1, 2, 3], $discountDTO->target_ids);
    }

    public function test_it_can_create_discount_dto_with_nullable_properties()
    {
        // Tạo DiscountDTO với một số thuộc tính null
        $discountDTO = new DiscountDTO(
            'Giảm giá mùa hè',  // name
            null,                // discount_type
            null,                // discount_value
            null,                // target_type
            null,                // min_purchase_amount
            null,                // max_discount_amount
            null,                // start_date
            null,                // end_date
            null,                // description
            true,                // is_active
            []                   // target_ids
        );

        // Kiểm tra các thuộc tính
        $this->assertEquals('Giảm giá mùa hè', $discountDTO->name);
        $this->assertNull($discountDTO->discount_type);
        $this->assertNull($discountDTO->discount_value);
        $this->assertNull($discountDTO->target_type);
        $this->assertNull($discountDTO->min_purchase_amount);
        $this->assertNull($discountDTO->max_discount_amount);
        $this->assertNull($discountDTO->start_date);
        $this->assertNull($discountDTO->end_date);
        $this->assertNull($discountDTO->description);
        $this->assertTrue($discountDTO->is_active);
        $this->assertEquals([], $discountDTO->target_ids);
    }

    public function test_it_can_create_from_request_data_for_book_discount()
    {
        // Dữ liệu request giả lập cho discount sách
        $requestData = [
          'data' => [
            'attributes' => [
              'name' => 'Giảm giá mùa hè',
              'discount_type' => 'percentage',
              'discount_value' => 10.5,
              'target_type' => 'book',
              'min_purchase_amount' => 100000,
              'max_discount_amount' => 50000,
              'start_date' => '2023-06-01',
              'end_date' => '2023-08-31',
              'description' => 'Áp dụng cho sách',
              'is_active' => true,
            ],
            'relationships' => [
              'targets' => [
                'data' => [
                  ['id' => 1, 'type' => 'book'],
                  ['id' => 2, 'type' => 'book'],
                ],
              ],
            ],
          ],
        ];

        // Tạo DTO từ request
        $discountDTO = DiscountDTO::fromRequest($requestData);

        // Kiểm tra các thuộc tính
        $this->assertEquals('Giảm giá mùa hè', $discountDTO->name);
        $this->assertEquals('percentage', $discountDTO->discount_type);
        $this->assertEquals(10.5, $discountDTO->discount_value);
        $this->assertEquals('book', $discountDTO->target_type);
        $this->assertEquals(100000, $discountDTO->min_purchase_amount);
        $this->assertEquals(50000, $discountDTO->max_discount_amount);
        $this->assertEquals('2023-06-01', $discountDTO->start_date);
        $this->assertEquals('2023-08-31', $discountDTO->end_date);
        $this->assertEquals('Áp dụng cho sách', $discountDTO->description);
        $this->assertTrue($discountDTO->is_active);
        $this->assertEquals([1, 2], $discountDTO->target_ids);
    }

    public function test_it_can_create_from_request_data_for_order_discount()
    {
        // Dữ liệu request giả lập cho discount đơn hàng
        $requestData = [
          'data' => [
            'attributes' => [
              'name' => 'Giảm giá đơn hàng',
              'discount_type' => 'fixed',
              'discount_value' => 50000,
              'target_type' => 'order',
              'min_purchase_amount' => 500000,
              'max_discount_amount' => 100000,
              'start_date' => '2023-06-01',
              'end_date' => '2023-08-31',
              'description' => 'Áp dụng cho đơn hàng',
              'is_active' => true,
            ],
            // Không có relationships targets cho order discount
          ],
        ];

        // Tạo DTO từ request
        $discountDTO = DiscountDTO::fromRequest($requestData);

        // Kiểm tra các thuộc tính
        $this->assertEquals('Giảm giá đơn hàng', $discountDTO->name);
        $this->assertEquals('fixed', $discountDTO->discount_type);
        $this->assertEquals(50000, $discountDTO->discount_value);
        $this->assertEquals('order', $discountDTO->target_type);
        $this->assertEquals(500000, $discountDTO->min_purchase_amount);
        $this->assertEquals(100000, $discountDTO->max_discount_amount);
        $this->assertEquals('2023-06-01', $discountDTO->start_date);
        $this->assertEquals('2023-08-31', $discountDTO->end_date);
        $this->assertEquals('Áp dụng cho đơn hàng', $discountDTO->description);
        $this->assertTrue($discountDTO->is_active);
        $this->assertEquals([], $discountDTO->target_ids); // Không có targets cho order discount
    }

    public function test_it_can_convert_to_array()
    {
        // Tạo DiscountDTO
        $discountDTO = new DiscountDTO(
            'Giảm giá mùa hè',
            'percentage',
            10.5,
            'book',
            100000,            // min_purchase_amount
            50000,             // max_discount_amount
            '2023-06-01',
            '2023-08-31',
            'Mô tả giảm giá',
            true,
            []
        );

        // Chuyển đổi sang array
        $array = $discountDTO->toArray();

        // Kiểm tra array
        $this->assertIsArray($array);
        $this->assertEquals('Giảm giá mùa hè', $array['name']);
        $this->assertEquals('percentage', $array['discount_type']);
        $this->assertEquals(10.5, $array['discount_value']);
        $this->assertEquals('book', $array['target_type']);
        $this->assertEquals(100000, $array['min_purchase_amount']);
        $this->assertEquals(50000, $array['max_discount_amount']);
        $this->assertEquals('2023-06-01', $array['start_date']);
        $this->assertEquals('2023-08-31', $array['end_date']);
        $this->assertEquals('Mô tả giảm giá', $array['description']);
        $this->assertEquals(true, $array['is_active']);
    }

    public function test_it_only_includes_non_null_values_in_array()
    {
        // Tạo DiscountDTO với một số thuộc tính null
        $discountDTO = new DiscountDTO(
            'Giảm giá mùa hè',  // name
            null,                // discount_type
            null,                // discount_value
            'book',              // target_type
            null,                // min_purchase_amount
            null,                // max_discount_amount
            null,                // start_date
            null,                // end_date
            null,                // description
            true,                // is_active
            []                   // target_ids
        );

        // Chuyển đổi sang array
        $array = $discountDTO->toArray();

        // Kiểm tra array chỉ chứa các thuộc tính không null
        $this->assertIsArray($array);
        $this->assertEquals('Giảm giá mùa hè', $array['name']);
        $this->assertEquals('book', $array['target_type']);
        $this->assertArrayNotHasKey('discount_type', $array);
        $this->assertArrayNotHasKey('discount_value', $array);
        $this->assertArrayNotHasKey('min_purchase_amount', $array);
        $this->assertArrayNotHasKey('max_discount_amount', $array);
        $this->assertArrayNotHasKey('start_date', $array);
        $this->assertArrayNotHasKey('end_date', $array);
        $this->assertArrayNotHasKey('description', $array);
        $this->assertEquals(true, $array['is_active']);
    }

    public function test_discount_with_min_purchase_amount_and_max_discount_amount()
    {
        // Tạo DiscountDTO với min_purchase_amount và max_discount_amount
        $discountDTO = new DiscountDTO(
            'Giảm giá có điều kiện',
            'percentage',
            20.0, // 20%
            'order',
            500000, // Đơn hàng tối thiểu 500k
            100000, // Giảm tối đa 100k
            '2023-06-01',
            '2023-08-31',
            'Giảm 20% tối đa 100k cho đơn từ 500k',
            true,
            []
        );

        // Kiểm tra các thuộc tính
        $this->assertEquals('Giảm giá có điều kiện', $discountDTO->name);
        $this->assertEquals('percentage', $discountDTO->discount_type);
        $this->assertEquals(20.0, $discountDTO->discount_value);
        $this->assertEquals('order', $discountDTO->target_type);
        $this->assertEquals(500000, $discountDTO->min_purchase_amount);
        $this->assertEquals(100000, $discountDTO->max_discount_amount);

        // Chuyển đổi sang array
        $array = $discountDTO->toArray();

        // Kiểm tra các thuộc tính trong array
        $this->assertEquals(500000, $array['min_purchase_amount']);
        $this->assertEquals(100000, $array['max_discount_amount']);
    }
}
