<?php

namespace Tests\Feature\Api\V1\Discount;

use App\DTOs\DiscountDTO;
use App\Models\Book;
use App\Models\Discount;
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
        $this->discountService = new DiscountService;
    }

    public function test_it_can_get_all_discounts()
    {
        // Tạo một số mã giảm giá
        Discount::factory()->count(5)->create([
            'target_type' => 'book',
        ]);

        // Tạo request giả lập
        $request = new Request;

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
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'target_type' => 'book',
        ]);

        // Gọi method getDiscountById
        $result = $this->discountService->getDiscountById($discount->id);

        // Kiểm tra kết quả
        $this->assertEquals($discount->id, $result->id);
        $this->assertEquals('Giảm giá mùa hè', $result->name);
        $this->assertEquals('percentage', $result->discount_type);
        $this->assertEquals(10, $result->discount_value);
        $this->assertEquals('book', $result->target_type);
    }

    public function test_it_throws_exception_when_discount_not_found()
    {
        // Dự kiến lỗi ModelNotFoundException khi tìm mã giảm giá không tồn tại
        $this->expectException(ModelNotFoundException::class);

        // Gọi method getDiscountById với ID không tồn tại
        $this->discountService->getDiscountById('non-existent-id');
    }

    public function test_it_can_create_discount_for_books()
    {
        // Tạo một sách để áp dụng giảm giá
        $book = Book::factory()->create();

        // Tạo DiscountDTO
        $discountData = [
            'name' => 'Giảm giá mùa hè',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'target_type' => 'book',
            'min_purchase_amount' => 50000,
            'max_discount_amount' => 10000,
            'start_date' => '2023-06-01',
            'end_date' => '2023-08-31',
            'target_ids' => [$book->id],
        ];

        $discountDTO = new DiscountDTO(
            $discountData['name'],
            $discountData['discount_type'],
            $discountData['discount_value'],
            $discountData['target_type'],
            $discountData['min_purchase_amount'],
            $discountData['max_discount_amount'],
            $discountData['start_date'],
            $discountData['end_date'],
            null, // description
            true, // is_active
            $discountData['target_ids']
        );

        // Gọi method createDiscount
        $result = $this->discountService->createDiscount($discountDTO);

        // Kiểm tra kết quả
        $this->assertNotNull($result->id);
        $this->assertEquals('Giảm giá mùa hè', $result->name);
        $this->assertEquals('percentage', $result->discount_type);
        $this->assertEquals(10, $result->discount_value);
        $this->assertEquals('book', $result->target_type);
        $this->assertEquals(50000, $result->min_purchase_amount);
        $this->assertEquals(10000, $result->max_discount_amount);
        $this->assertEquals($result->start_date->format('Y-m-d'), '2023-06-01');
        $this->assertEquals($result->end_date->format('Y-m-d'), '2023-08-31');

        // Kiểm tra database
        $this->assertDatabaseHas('discounts', [
            'name' => 'Giảm giá mùa hè',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'target_type' => 'book',
            'min_purchase_amount' => 50000,
            'max_discount_amount' => 10000,
        ]);

        // Kiểm tra liên kết với sách
        $this->assertDatabaseHas('discount_targets', [
            'discount_id' => $result->id,
            'target_id' => $book->id,
        ]);
    }

    public function test_it_can_create_discount_for_orders()
    {
        // Tạo DiscountDTO cho đơn hàng
        $discountData = [
            'name' => 'Giảm giá đơn hàng',
            'discount_type' => 'fixed',
            'discount_value' => 50000,
            'target_type' => 'order',
            'min_purchase_amount' => 300000,
            'max_discount_amount' => null,
            'start_date' => '2023-06-01',
            'end_date' => '2023-08-31',
            'target_ids' => [], // Không cần target_ids cho đơn hàng
        ];

        $discountDTO = new DiscountDTO(
            $discountData['name'],
            $discountData['discount_type'],
            $discountData['discount_value'],
            $discountData['target_type'],
            $discountData['min_purchase_amount'],
            $discountData['max_discount_amount'],
            $discountData['start_date'],
            $discountData['end_date'],
            'Áp dụng cho tất cả đơn hàng', // description
            true, // is_active
            $discountData['target_ids']
        );

        // Gọi method createDiscount
        $result = $this->discountService->createDiscount($discountDTO);

        // Kiểm tra kết quả
        $this->assertNotNull($result->id);
        $this->assertEquals('Giảm giá đơn hàng', $result->name);
        $this->assertEquals('fixed', $result->discount_type);
        $this->assertEquals(50000, $result->discount_value);
        $this->assertEquals('order', $result->target_type);
        $this->assertEquals(300000, $result->min_purchase_amount);
        $this->assertNull($result->max_discount_amount);

        // Kiểm tra database
        $this->assertDatabaseHas('discounts', [
            'name' => 'Giảm giá đơn hàng',
            'discount_type' => 'fixed',
            'discount_value' => 50000,
            'target_type' => 'order',
            'min_purchase_amount' => 300000,
        ]);

        // Kiểm tra không có liên kết với đối tượng nào
        $this->assertDatabaseMissing('discount_targets', [
            'discount_id' => $result->id,
        ]);
    }

    public function test_it_restores_soft_deleted_discount_with_same_name()
    {
        // Tạo một sách để áp dụng giảm giá
        $book = Book::factory()->create();

        // Tạo một mã giảm giá và soft delete nó
        $discount = Discount::factory()->create([
            'name' => 'Giảm giá mùa hè',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'target_type' => 'book',
        ]);
        $discountId = $discount->id;
        $discount->delete();

        // Kiểm tra rằng mã giảm giá đã bị soft delete
        $this->assertSoftDeleted('discounts', [
            'id' => $discountId,
        ]);

        // Tạo DiscountDTO với name giống mã giảm giá đã xóa và target_ids phù hợp
        $discountDTO = new DiscountDTO(
            'Giảm giá mùa hè',
            'fixed',
            50000,
            'book',
            100000, // min_purchase_amount
            20000,  // max_discount_amount
            '2023-06-01',
            '2023-08-31',
            null, // description
            true, // is_active
            [$book->id] // Thêm target_ids khi target_type là book
        );

        // Gọi method createDiscount
        $result = $this->discountService->createDiscount($discountDTO);

        // Kiểm tra kết quả
        $this->assertEquals($discountId, $result->id); // ID phải giống mã giảm giá ban đầu vì đã restore
        $this->assertEquals('Giảm giá mùa hè', $result->name);
        $this->assertEquals('fixed', $result->discount_type); // Loại giảm giá đã được cập nhật
        $this->assertEquals(50000, $result->discount_value); // Giá trị giảm giá đã được cập nhật
        $this->assertEquals('book', $result->target_type);

        // Kiểm tra database rằng mã giảm giá không còn bị soft delete
        $this->assertDatabaseHas('discounts', [
            'id' => $discountId,
            'deleted_at' => null,
        ]);

        // Kiểm tra liên kết với sách
        $this->assertDatabaseHas('discount_targets', [
            'discount_id' => $discountId,
            'target_id' => $book->id,
        ]);
    }

    public function test_it_can_update_discount()
    {
        // Tạo một mã giảm giá
        $discount = Discount::factory()->create([
            'name' => 'Giảm giá ban đầu',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'target_type' => 'book',
            'start_date' => '2023-01-01',
            'end_date' => '2023-03-31',
        ]);

        // Tạo DiscountDTO cho cập nhật
        $discountDTO = new DiscountDTO(
            'Giảm giá đã cập nhật',
            'fixed',
            50000,
            'order', // Đổi từ book sang order
            200000, // min_purchase_amount
            null,   // max_discount_amount
            null,   // start_date
            null,   // end_date
            null,   // description
            true,   // is_active
            []      // target_ids
        );

        // Gọi method updateDiscount
        $result = $this->discountService->updateDiscount($discount->id, $discountDTO);

        // Kiểm tra kết quả
        $this->assertEquals($discount->id, $result->id);
        $this->assertEquals('Giảm giá đã cập nhật', $result->name);
        $this->assertEquals('fixed', $result->discount_type);
        $this->assertEquals(50000, $result->discount_value);
        $this->assertEquals('order', $result->target_type); // Kiểm tra target_type đã được cập nhật
        $this->assertEquals($result->start_date->format('Y-m-d'), '2023-01-01'); // Ngày bắt đầu không thay đổi
        $this->assertEquals($result->end_date->format('Y-m-d'), '2023-03-31'); // Ngày kết thúc không thay đổi

        // Kiểm tra database
        $this->assertDatabaseHas('discounts', [
            'id' => $discount->id,
            'name' => 'Giảm giá đã cập nhật',
            'discount_type' => 'fixed',
            'discount_value' => 50000,
            'target_type' => 'order',
        ]);
    }

    public function test_it_throws_validation_exception_when_updating_to_existing_name()
    {
        // Tạo hai mã giảm giá với name khác nhau
        $discount1 = Discount::factory()->create([
            'name' => 'Giảm giá thứ nhất',
            'target_type' => 'book',
        ]);

        $discount2 = Discount::factory()->create([
            'name' => 'Giảm giá thứ hai',
            'target_type' => 'book',
        ]);

        // Tạo DiscountDTO cập nhật discount2 thành name của discount1
        $discountDTO = new DiscountDTO(
            'Giảm giá thứ nhất', // Name giống discount1
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null, // description
            true, // is_active
            []   // target_ids
        );

        // Dự kiến lỗi ValidationException
        $this->expectException(ValidationException::class);

        // Gọi method updateDiscount và kỳ vọng ngoại lệ
        $this->discountService->updateDiscount($discount2->id, $discountDTO);
    }

    public function test_it_can_delete_discount()
    {
        // Tạo một mã giảm giá
        $discount = Discount::factory()->create([
            'target_type' => 'book',
        ]);

        // Gọi method deleteDiscount
        $result = $this->discountService->deleteDiscount($discount->id);

        // Kiểm tra kết quả
        $this->assertEquals($discount->id, $result->id);

        // Kiểm tra rằng mã giảm giá đã bị soft delete
        $this->assertSoftDeleted('discounts', [
            'id' => $discount->id,
        ]);
    }

    public function test_it_can_calculate_discounted_price_for_book()
    {
        // Tạo một sách
        $book = Book::factory()->create([
            'price' => 100000,
        ]);

        // Tạo một mã giảm giá phần trăm cho sách
        $percentDiscount = Discount::factory()->create([
            'name' => 'Giảm giá 10%',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'target_type' => 'book',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'is_active' => true,
        ]);

        // Liên kết sách với mã giảm giá
        $book->discounts()->attach($percentDiscount->id);

        // Tính giá sau khi giảm
        $discountedPrice = $this->discountService->calculateDiscountedPrice($book);

        // Kiểm tra giá sau khi giảm 10%
        $this->assertEquals(90000, $discountedPrice);
    }

    public function test_it_applies_highest_discount_when_multiple_discounts_exist()
    {
        // Tạo một sách
        $book = Book::factory()->create([
            'price' => 100000,
        ]);

        // Tạo hai mã giảm giá với giá trị khác nhau
        $discount1 = Discount::factory()->create([
            'name' => 'Giảm giá 10%',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'target_type' => 'book',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'is_active' => true,
        ]);

        $discount2 = Discount::factory()->create([
            'name' => 'Giảm giá 20%',
            'discount_type' => 'percentage',
            'discount_value' => 20,
            'target_type' => 'book',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'is_active' => true,
        ]);

        // Liên kết sách với cả hai mã giảm giá
        $book->discounts()->attach([$discount1->id, $discount2->id]);

        // Tính giá sau khi giảm
        $discountedPrice = $this->discountService->calculateDiscountedPrice($book);

        // Kiểm tra giá sau khi giảm (áp dụng giảm giá cao nhất - 20%)
        $this->assertEquals(80000, $discountedPrice);
    }

    public function test_it_applies_discount_with_min_purchase_amount_condition()
    {
        // Tạo một sách với giá 80.000
        $book = Book::factory()->create([
            'price' => 80000,
        ]);

        // Tạo mã giảm giá với min_purchase_amount = 100.000 (lớn hơn giá sách)
        $discount = Discount::factory()->create([
            'name' => 'Giảm giá điều kiện',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'target_type' => 'book',
            'min_purchase_amount' => 100000, // Chỉ áp dụng cho sách >= 100.000
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'is_active' => true,
        ]);

        // Liên kết sách với mã giảm giá
        $book->discounts()->attach($discount->id);

        // Tính giá sau khi giảm - KHÔNG được áp dụng vì không đạt min_purchase_amount
        $discountedPrice = $this->discountService->calculateDiscountedPrice($book);

        // Kiểm tra giá KHÔNG giảm vì không đạt điều kiện
        $this->assertEquals(80000, $discountedPrice);

        // Tạo một sách khác với giá cao hơn min_purchase_amount
        $expensiveBook = Book::factory()->create([
            'price' => 150000,
        ]);

        // Liên kết sách với cùng mã giảm giá
        $expensiveBook->discounts()->attach($discount->id);

        // Tính giá sau khi giảm - ĐƯỢC áp dụng vì đạt min_purchase_amount
        $discountedPrice = $this->discountService->calculateDiscountedPrice($expensiveBook);

        // Kiểm tra giá đã giảm 10%
        $this->assertEquals(135000, $discountedPrice);
    }

    public function test_it_applies_discount_with_max_discount_amount_limit()
    {
        // Tạo một sách với giá cao
        $book = Book::factory()->create([
            'price' => 500000,
        ]);

        // Tạo mã giảm giá PHẦN TRĂM với max_discount_amount = 30.000
        $percentageDiscount = Discount::factory()->create([
            'name' => 'Giảm giá 20% tối đa 30.000',
            'discount_type' => 'percentage',
            'discount_value' => 20, // 20%
            'target_type' => 'book',
            'max_discount_amount' => 30000, // Giảm tối đa 30.000
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'is_active' => true,
        ]);

        // Liên kết sách với mã giảm giá
        $book->discounts()->attach($percentageDiscount->id);

        // Tính giá sau khi giảm - áp dụng giới hạn max_discount_amount
        $discountedPrice = $this->discountService->calculateDiscountedPrice($book);

        // Lẽ ra giảm 20% = 100.000, nhưng do giới hạn 30.000 nên chỉ giảm 30.000
        $this->assertEquals(470000, $discountedPrice);

        // Tạo mã giảm giá CỐ ĐỊNH với max_discount_amount = 40.000
        $fixedDiscount = Discount::factory()->create([
            'name' => 'Giảm giá 50.000 tối đa 40.000',
            'discount_type' => 'fixed',
            'discount_value' => 50000, // Giảm 50.000
            'target_type' => 'book',
            'max_discount_amount' => 40000, // Giảm tối đa 40.000
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'is_active' => true,
        ]);

        // Xóa liên kết cũ và thêm liên kết mới
        $book->discounts()->detach();
        $book->discounts()->attach($fixedDiscount->id);

        // Tính giá sau khi giảm - áp dụng giới hạn max_discount_amount
        $discountedPrice = $this->discountService->calculateDiscountedPrice($book);

        // Lẽ ra giảm 50.000, nhưng do giới hạn 40.000 nên chỉ giảm 40.000
        $this->assertEquals(460000, $discountedPrice);
    }

    public function test_it_applies_both_min_purchase_and_max_discount_conditions()
    {
        // Tạo một sách với giá trung bình
        $book = Book::factory()->create([
            'price' => 200000,
        ]);

        // Tạo mã giảm giá với cả hai điều kiện
        $discount = Discount::factory()->create([
            'name' => 'Giảm giá có điều kiện',
            'discount_type' => 'percentage',
            'discount_value' => 15, // 15%
            'target_type' => 'book',
            'min_purchase_amount' => 150000, // Chỉ áp dụng cho sách >= 150.000
            'max_discount_amount' => 25000, // Giảm tối đa 25.000
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'is_active' => true,
        ]);

        // Liên kết sách với mã giảm giá
        $book->discounts()->attach($discount->id);

        // Tính giá sau khi giảm
        $discountedPrice = $this->discountService->calculateDiscountedPrice($book);

        // Lẽ ra giảm 15% = 30.000, nhưng do giới hạn 25.000 nên chỉ giảm 25.000
        $this->assertEquals(175000, $discountedPrice);

        // Tạo một sách khác với giá thấp hơn min_purchase_amount
        $cheapBook = Book::factory()->create([
            'price' => 100000,
        ]);

        // Liên kết sách với cùng mã giảm giá
        $cheapBook->discounts()->attach($discount->id);

        // Tính giá sau khi giảm - KHÔNG được áp dụng vì không đạt min_purchase_amount
        $cheapDiscountedPrice = $this->discountService->calculateDiscountedPrice($cheapBook);

        // Kiểm tra giá KHÔNG giảm
        $this->assertEquals(100000, $cheapDiscountedPrice);
    }
}
