<?php

namespace Tests\Unit\DTOs;

use App\DTOs\Discount\DiscountDTO;
use PHPUnit\Framework\TestCase;

class DiscountDTOTest extends TestCase
{
  public function test_it_can_create_discount_dto_with_all_properties()
  {
    // Tạo DiscountDTO với tất cả thuộc tính
    $discountDTO = new DiscountDTO(
      'Giảm giá mùa hè',
      'percent',
      10.5,
      'book',
      '2023-06-01',
      '2023-08-31',
      'Mô tả giảm giá',
      true,
      [1, 2, 3]
    );

    // Kiểm tra các thuộc tính
    $this->assertEquals('Giảm giá mùa hè', $discountDTO->name);
    $this->assertEquals('percent', $discountDTO->discount_type);
    $this->assertEquals(10.5, $discountDTO->discount_value);
    $this->assertEquals('book', $discountDTO->target_type);
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
      'Giảm giá mùa hè',
      null,
      null,
      null,
      null,
      null
    );

    // Kiểm tra các thuộc tính
    $this->assertEquals('Giảm giá mùa hè', $discountDTO->name);
    $this->assertNull($discountDTO->discount_type);
    $this->assertNull($discountDTO->discount_value);
    $this->assertNull($discountDTO->target_type);
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
          'discount_type' => 'percent',
          'discount_value' => 10.5,
          'target_type' => 'book',
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
    $this->assertEquals('percent', $discountDTO->discount_type);
    $this->assertEquals(10.5, $discountDTO->discount_value);
    $this->assertEquals('book', $discountDTO->target_type);
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
      'percent',
      10.5,
      'book',
      '2023-06-01',
      '2023-08-31',
      'Mô tả giảm giá',
      true
    );

    // Chuyển đổi sang array
    $array = $discountDTO->toArray();

    // Kiểm tra array
    $this->assertIsArray($array);
    $this->assertEquals('Giảm giá mùa hè', $array['name']);
    $this->assertEquals('percent', $array['discount_type']);
    $this->assertEquals(10.5, $array['discount_value']);
    $this->assertEquals('book', $array['target_type']);
    $this->assertEquals('2023-06-01', $array['start_date']);
    $this->assertEquals('2023-08-31', $array['end_date']);
    $this->assertEquals('Mô tả giảm giá', $array['description']);
    $this->assertEquals(true, $array['is_active']);
  }

  public function test_it_only_includes_non_null_values_in_array()
  {
    // Tạo DiscountDTO với một số thuộc tính null
    $discountDTO = new DiscountDTO(
      'Giảm giá mùa hè',
      null,
      null,
      'book',
      null,
      null
    );

    // Chuyển đổi sang array
    $array = $discountDTO->toArray();

    // Kiểm tra array chỉ chứa các thuộc tính không null
    $this->assertIsArray($array);
    $this->assertEquals('Giảm giá mùa hè', $array['name']);
    $this->assertEquals('book', $array['target_type']);
    $this->assertArrayNotHasKey('discount_type', $array);
    $this->assertArrayNotHasKey('discount_value', $array);
    $this->assertArrayNotHasKey('start_date', $array);
    $this->assertArrayNotHasKey('end_date', $array);
    $this->assertArrayNotHasKey('description', $array);
    $this->assertEquals(true, $array['is_active']);
  }
}
