<?php

namespace Tests\Unit\Services;

use App\DTOs\Discount\DiscountDTO;
use App\Models\Discount;
use App\Models\DiscountTarget;
use App\Services\DiscountService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DiscountServiceTest extends TestCase
{
    use RefreshDatabase;

    private DiscountService $discountService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->discountService = new DiscountService();
    }

    public function test_it_can_get_all_discounts()
    {
        // Tạo một số mã giảm giá
        Discount::factory()->count(5)->create();

        // Tạo request giả lập
        $request = new Request();

        // Gọi method getAllDiscounts
        $result = $this->discountService->getAllDiscounts($request, 10);

        // Kiểm tra kết quả
        $this->assertEquals(5, $result->count());
        $this->assertEquals(1, $result->currentPage());
        $this->assertEquals(10, $result->perPage());
    }

    public function test_it_can_get_discount_by_id()
    {
        // Tạo một mã giảm giá
        $discount = Discount::factory()->create([
            'name' => 'Giảm giá mùa hè',
            'discount_type' => 'percent',
            'discount_value' => 10,
        ]);

        // Gọi method getDiscountById
        $result = $this->discountService->getDiscountById($discount->id);

        // Kiểm tra kết quả
        $this->assertEquals($discount->id, $result->id);
        $this->assertEquals('Giảm giá mùa hè', $result->name);
        $this->assertEquals('percent', $result->discount_type);
        $this->assertEquals(10, $result->discount_value);
    }

    public function test_it_throws_exception_when_discount_not_found()
    {
        // Dự kiến lỗi ModelNotFoundException khi tìm mã giảm giá không tồn tại
        $this->expectException(ModelNotFoundException::class);

        // Gọi method getDiscountById với ID không tồn tại
        $this->discountService->getDiscountById('non-existent-id');
    }

    public function test_it_can_create_discount()
    {
        // Tạo DiscountDTO
        $discountData = [
            'name' => 'Giảm giá mùa hè',
            'discount_type' => 'percent',
            'discount_value' => 10,
            'start_date' => '2023-06-01',
            'end_date' => '2023-08-31',
            'target_ids' => [1, 2, 3],
        ];

        $discountDTO = new DiscountDTO(
            $discountData['name'],
            $discountData['discount_type'],
            $discountData['discount_value'],
            $discountData['start_date'],
            $discountData['end_date'],
            $discountData['target_ids']
        );

        // Tạo mock điểm đến cho target_ids
        $this->mock(\App\Models\Book::class, function ($mock) {
            $mock->shouldReceive('findOrFail')->andReturn(new \App\Models\Book());
        });

        // Gọi method createDiscount
        $result = $this->discountService->createDiscount($discountDTO);

        // Kiểm tra kết quả
        $this->assertNotNull($result->id);
        $this->assertEquals('Giảm giá mùa hè', $result->name);
        $this->assertEquals('percent', $result->discount_type);
        $this->assertEquals(10, $result->discount_value);
        $this->assertEquals($result->start_date->format('Y-m-d'), '2023-06-01');
        $this->assertEquals($result->end_date->format('Y-m-d'), '2023-08-31');

        // Kiểm tra database
        $this->assertDatabaseHas('discounts', [
            'name' => 'Giảm giá mùa hè',
            'discount_type' => 'percent',
            'discount_value' => 10,
        ]);
    }

    public function test_it_restores_soft_deleted_discount_with_same_name()
    {
        // Tạo một mã giảm giá và soft delete nó
        $discount = Discount::factory()->create([
            'name' => 'Giảm giá mùa hè',
            'discount_type' => 'percent',
            'discount_value' => 10,
        ]);
        $discountId = $discount->id;
        $discount->delete();

        // Kiểm tra rằng mã giảm giá đã bị soft delete
        $this->assertSoftDeleted('discounts', [
            'id' => $discountId,
        ]);

        // Tạo DiscountDTO với name giống mã giảm giá đã xóa
        $discountDTO = new DiscountDTO(
            'Giảm giá mùa hè',
            'fixed',
            50000,
            '2023-06-01',
            '2023-08-31'
        );

        // Gọi method createDiscount
        $result = $this->discountService->createDiscount($discountDTO);

        // Kiểm tra kết quả
        $this->assertEquals($discountId, $result->id); // ID phải giống mã giảm giá ban đầu vì đã restore
        $this->assertEquals('Giảm giá mùa hè', $result->name);
        $this->assertEquals('fixed', $result->discount_type); // Loại giảm giá đã được cập nhật
        $this->assertEquals(50000, $result->discount_value); // Giá trị giảm giá đã được cập nhật

        // Kiểm tra database rằng mã giảm giá không còn bị soft delete
        $this->assertDatabaseHas('discounts', [
            'id' => $discountId,
            'deleted_at' => null,
        ]);
    }

    public function test_it_can_update_discount()
    {
        // Tạo một mã giảm giá
        $discount = Discount::factory()->create([
            'name' => 'Giảm giá ban đầu',
            'discount_type' => 'percent',
            'discount_value' => 10,
            'start_date' => '2023-01-01',
            'end_date' => '2023-03-31',
        ]);

        // Tạo DiscountDTO cho cập nhật
        $discountDTO = new DiscountDTO(
            'Giảm giá đã cập nhật',
            'fixed',
            50000,
            null,
            null
        );

        // Gọi method updateDiscount
        $result = $this->discountService->updateDiscount($discount->id, $discountDTO);

        // Kiểm tra kết quả
        $this->assertEquals($discount->id, $result->id);
        $this->assertEquals('Giảm giá đã cập nhật', $result->name);
        $this->assertEquals('fixed', $result->discount_type);
        $this->assertEquals(50000, $result->discount_value);
        $this->assertEquals($result->start_date->format('Y-m-d'), '2023-01-01'); // Ngày bắt đầu không thay đổi
        $this->assertEquals($result->end_date->format('Y-m-d'), '2023-03-31'); // Ngày kết thúc không thay đổi

        // Kiểm tra database
        $this->assertDatabaseHas('discounts', [
            'id' => $discount->id,
            'name' => 'Giảm giá đã cập nhật',
            'discount_type' => 'fixed',
            'discount_value' => 50000,
        ]);
    }

    public function test_it_throws_validation_exception_when_updating_to_existing_name()
    {
        // Tạo hai mã giảm giá với name khác nhau
        $discount1 = Discount::factory()->create([
            'name' => 'Giảm giá thứ nhất',
        ]);

        $discount2 = Discount::factory()->create([
            'name' => 'Giảm giá thứ hai',
        ]);

        // Tạo DiscountDTO cập nhật discount2 thành name của discount1
        $discountDTO = new DiscountDTO(
            'Giảm giá thứ nhất', // Name giống discount1
            null,
            null,
            null,
            null
        );

        // Dự kiến lỗi ValidationException
        $this->expectException(ValidationException::class);

        // Gọi method updateDiscount và kỳ vọng ngoại lệ
        $this->discountService->updateDiscount($discount2->id, $discountDTO);
    }

    public function test_it_can_delete_discount()
    {
        // Tạo một mã giảm giá
        $discount = Discount::factory()->create();

        // Gọi method deleteDiscount
        $result = $this->discountService->deleteDiscount($discount->id);

        // Kiểm tra kết quả
        $this->assertEquals($discount->id, $result->id);

        // Kiểm tra rằng mã giảm giá đã bị soft delete
        $this->assertSoftDeleted('discounts', [
            'id' => $discount->id,
        ]);
    }
}
