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
            'percentage',
            10.5,
            '2023-06-01',
            '2023-08-31',
            [1, 2, 3]
        );

        // Kiểm tra các thuộc tính
        $this->assertEquals('Giảm giá mùa hè', $discountDTO->name);
        $this->assertEquals('percentage', $discountDTO->discount_type);
        $this->assertEquals(10.5, $discountDTO->discount_value);
        $this->assertEquals('2023-06-01', $discountDTO->start_date);
        $this->assertEquals('2023-08-31', $discountDTO->end_date);
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
            null
        );

        // Kiểm tra các thuộc tính
        $this->assertEquals('Giảm giá mùa hè', $discountDTO->name);
        $this->assertNull($discountDTO->discount_type);
        $this->assertNull($discountDTO->discount_value);
        $this->assertNull($discountDTO->start_date);
        $this->assertNull($discountDTO->end_date);
        $this->assertEquals([], $discountDTO->target_ids);
    }

    public function test_it_can_create_from_request_data()
    {
        // Dữ liệu request giả lập
        $requestData = [
            'data' => [
                'attributes' => [
                    'name' => 'Giảm giá mùa hè',
                    'discount_type' => 'percentage',
                    'discount_value' => 10.5,
                    'start_date' => '2023-06-01',
                    'end_date' => '2023-08-31',
                ],
                'relationships' => [
                    'targets' => [
                        'data' => [
                            ['id' => 1, 'type' => 'book'],
                            ['id' => 2, 'type' => 'genre'],
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
        $this->assertEquals('2023-06-01', $discountDTO->start_date);
        $this->assertEquals('2023-08-31', $discountDTO->end_date);
        $this->assertEquals([1, 2], $discountDTO->target_ids);
    }

    public function test_it_can_convert_to_array()
    {
        // Tạo DiscountDTO
        $discountDTO = new DiscountDTO(
            'Giảm giá mùa hè',
            'percentage',
            10.5,
            '2023-06-01',
            '2023-08-31'
        );

        // Chuyển đổi sang array
        $array = $discountDTO->toArray();

        // Kiểm tra array
        $this->assertIsArray($array);
        $this->assertEquals('Giảm giá mùa hè', $array['name']);
        $this->assertEquals('percentage', $array['discount_type']);
        $this->assertEquals(10.5, $array['discount_value']);
        $this->assertEquals('2023-06-01', $array['start_date']);
        $this->assertEquals('2023-08-31', $array['end_date']);
    }

    public function test_it_only_includes_non_null_values_in_array()
    {
        // Tạo DiscountDTO với một số thuộc tính null
        $discountDTO = new DiscountDTO(
            'Giảm giá mùa hè',
            null,
            null,
            null,
            null
        );

        // Chuyển đổi sang array
        $array = $discountDTO->toArray();

        // Kiểm tra array chỉ chứa các thuộc tính không null
        $this->assertIsArray($array);
        $this->assertEquals('Giảm giá mùa hè', $array['name']);
        $this->assertArrayNotHasKey('discount_type', $array);
        $this->assertArrayNotHasKey('discount_value', $array);
        $this->assertArrayNotHasKey('start_date', $array);
        $this->assertArrayNotHasKey('end_date', $array);
    }
}
