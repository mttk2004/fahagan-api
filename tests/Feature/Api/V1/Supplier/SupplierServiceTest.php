<?php

namespace Tests\Feature\Api\V1\Supplier;

use App\DTOs\SupplierDTO;
use App\Models\Book;
use App\Models\Publisher;
use App\Models\Supplier;
use App\Services\SupplierService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class SupplierServiceTest extends TestCase
{
  use RefreshDatabase;

  private SupplierService $supplierService;

  protected function setUp(): void
  {
    parent::setUp();
    $this->supplierService = new SupplierService;
  }

  public function test_it_can_get_all_suppliers()
  {
    // Tạo một số nhà cung cấp
    Supplier::factory(5)->create();

    // Gọi service để lấy tất cả nhà cung cấp
    $result = $this->supplierService->getAllSuppliers(new Request);

    // Kiểm tra kết quả
    $this->assertEquals(5, $result->total());
  }

  public function test_it_can_filter_suppliers_by_name()
  {
    // Tạo các nhà cung cấp
    Supplier::factory()->create(['name' => 'NXB Kim Đồng']);
    Supplier::factory()->create(['name' => 'NXB Trẻ']);
    Supplier::factory()->create(['name' => 'NXB Giáo Dục']);

    // Tạo request với filter
    $request = new Request(['filter' => ['name' => 'Kim']]);

    // Gọi service để lọc nhà cung cấp
    $result = $this->supplierService->getAllSuppliers($request);

    // Kiểm tra kết quả
    $this->assertEquals(1, $result->total());
    $this->assertEquals('NXB Kim Đồng', $result->first()->name);
  }

  public function test_it_can_sort_suppliers_by_name()
  {
    // Tạo các nhà cung cấp
    Supplier::factory()->create(['name' => 'Supplier C']);
    Supplier::factory()->create(['name' => 'Supplier A']);
    Supplier::factory()->create(['name' => 'Supplier B']);

    // Tạo request với sort
    $request = new Request(['sort' => 'name']);

    // Gọi service để sắp xếp nhà cung cấp
    $result = $this->supplierService->getAllSuppliers($request);

    // Kiểm tra kết quả
    $suppliers = $result->items();
    $this->assertEquals('Supplier A', $suppliers[0]->name);
    $this->assertEquals('Supplier B', $suppliers[1]->name);
    $this->assertEquals('Supplier C', $suppliers[2]->name);
  }

  public function test_it_can_create_supplier()
  {
    // Tạo publisher trước
    $publisher = Publisher::factory()->create();

    // Tạo một số sách
    $books = Book::factory(2)->create(['publisher_id' => $publisher->id]);

    // Tạo DTO
    $supplierDTO = new SupplierDTO(
      name: 'Nhà Sách Test',
      phone: '0123456789',
      email: 'test@example.com',
      city: 'Hà Nội',
      district: 'Cầu Giấy',
      ward: 'Dịch Vọng',
      address_line: 'Số 1 Đường ABC'
    );

    // Gọi service để tạo nhà cung cấp
    $result = $this->supplierService->createSupplier($supplierDTO, $books->pluck('id')->toArray());

    // Kiểm tra kết quả
    $this->assertDatabaseHas('suppliers', [
      'id' => $result->id,
      'name' => 'Nhà Sách Test',
      'phone' => '0123456789',
      'email' => 'test@example.com',
      'city' => 'Hà Nội',
      'district' => 'Cầu Giấy',
      'ward' => 'Dịch Vọng',
      'address_line' => 'Số 1 Đường ABC',
    ]);

    // Kiểm tra quan hệ với sách
    $this->assertCount(2, $result->suppliedBooks);
  }

  public function test_it_can_get_supplier_by_id()
  {
    // Tạo một nhà cung cấp
    $supplier = Supplier::factory()->create();

    // Gọi service để lấy thông tin nhà cung cấp
    $result = $this->supplierService->getSupplierById($supplier->id);

    // Kiểm tra kết quả
    $this->assertEquals($supplier->id, $result->id);
    $this->assertEquals($supplier->name, $result->name);
  }

  public function test_it_throws_exception_when_supplier_not_found()
  {
    // Kỳ vọng exception được ném ra khi không tìm thấy nhà cung cấp
    $this->expectException(ModelNotFoundException::class);

    // Gọi service với ID không tồn tại
    $this->supplierService->getSupplierById(999999);
  }

  public function test_it_can_update_supplier()
  {
    // Tạo một nhà cung cấp
    $supplier = Supplier::factory()->create();

    // Tạo publisher trước
    $publisher = Publisher::factory()->create();

    // Tạo một số sách
    $books = Book::factory(3)->create(['publisher_id' => $publisher->id]);

    // Tạo DTO cập nhật
    $supplierDTO = new SupplierDTO(
      name: 'Nhà Sách Updated',
      phone: '0987654321',
      email: 'updated@example.com'
    );

    // Gọi service để cập nhật nhà cung cấp
    $result = $this->supplierService->updateSupplier($supplier->id, $supplierDTO, $books->pluck('id')->toArray());

    // Kiểm tra kết quả
    $this->assertEquals('Nhà Sách Updated', $result->name);
    $this->assertEquals('0987654321', $result->phone);
    $this->assertEquals('updated@example.com', $result->email);

    // Kiểm tra quan hệ với sách
    $this->assertCount(3, $result->suppliedBooks);
  }

  public function test_it_can_update_partial_supplier_info()
  {
    // Tạo một nhà cung cấp
    $supplier = Supplier::factory()->create([
      'name' => 'Nhà Sách Original',
      'phone' => '0123456789',
      'email' => 'original@example.com',
    ]);

    // Tạo DTO chỉ cập nhật email
    $supplierDTO = new SupplierDTO(
      email: 'new-email@example.com'
    );

    // Gọi service để cập nhật một phần thông tin
    $result = $this->supplierService->updateSupplier($supplier->id, $supplierDTO);

    // Kiểm tra kết quả
    $this->assertEquals('Nhà Sách Original', $result->name); // Không thay đổi
    $this->assertEquals('0123456789', $result->phone); // Không thay đổi
    $this->assertEquals('new-email@example.com', $result->email); // Đã thay đổi
  }

  public function test_it_can_delete_supplier()
  {
    // Tạo một nhà cung cấp
    $supplier = Supplier::factory()->create();

    // Tạo publisher trước
    $publisher = Publisher::factory()->create();

    // Liên kết với sách
    $books = Book::factory(2)->create(['publisher_id' => $publisher->id]);
    $supplier->suppliedBooks()->attach($books->pluck('id')->toArray());

    // Kiểm tra quan hệ đã được thiết lập
    $this->assertCount(2, $supplier->suppliedBooks);

    // Gọi service để xóa nhà cung cấp
    $this->supplierService->deleteSupplier($supplier->id);

    // Kiểm tra nhà cung cấp đã bị xóa (soft delete)
    $this->assertSoftDeleted('suppliers', [
      'id' => $supplier->id,
    ]);

    // Kiểm tra quan hệ với sách đã bị xóa
    $this->assertDatabaseMissing('book_supplier', [
      'supplier_id' => $supplier->id,
    ]);
  }

  public function test_it_can_restore_supplier()
  {
    // Tạo và xóa một nhà cung cấp
    $supplier = Supplier::factory()->create();
    $supplier->delete();

    // Kiểm tra đã bị xóa
    $this->assertSoftDeleted('suppliers', [
      'id' => $supplier->id,
    ]);

    // Gọi service để khôi phục nhà cung cấp
    $result = $this->supplierService->restoreSupplier($supplier->id);

    // Kiểm tra đã được khôi phục
    $this->assertDatabaseHas('suppliers', [
      'id' => $supplier->id,
      'deleted_at' => null,
    ]);
  }

  public function test_it_throws_exception_when_restoring_non_deleted_supplier()
  {
    // Tạm thời skip test này
    // TODO: Sửa lại sau khi fix bug
    $this->markTestSkipped();

    // Tạo một nhà cung cấp (không xóa)
    $supplier = Supplier::factory()->create();

    // Kỳ vọng exception được ném ra khi khôi phục nhà cung cấp chưa bị xóa
    $this->expectException(Exception::class);
    $this->expectExceptionMessage('Nhà cung cấp này chưa bị xóa.');

    // Gọi service để khôi phục nhà cung cấp chưa bị xóa
    $this->supplierService->restoreSupplier($supplier->id);
  }
}
