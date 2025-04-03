<?php

namespace Tests\Feature\Api\V1\Publisher;

use App\DTOs\Publisher\PublisherDTO;
use App\Models\Publisher;
use App\Services\PublisherService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class PublisherServiceTest extends TestCase
{
    use RefreshDatabase;

    private PublisherService $publisherService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->publisherService = new PublisherService();
    }

    public function test_it_can_get_all_publishers()
    {
        // Tạo 5 nhà xuất bản
        Publisher::factory()->count(5)->create();

        // Tạo request rỗng
        $request = new Request();

        // Lấy danh sách nhà xuất bản
        $publishers = $this->publisherService->getAllPublishers($request);

        // Kiểm tra số lượng nhà xuất bản
        $this->assertEquals(5, $publishers->count());
    }

    public function test_it_can_filter_publishers_by_name()
    {
        // Tạo 3 nhà xuất bản với tên khác nhau
        Publisher::factory()->create(['name' => 'NXB Văn Học']);
        Publisher::factory()->create(['name' => 'NXB Trẻ']);
        Publisher::factory()->create(['name' => 'NXB Tổng Hợp']);

        // Tạo request với filter theo tên
        $request = new Request(['filter' => ['name' => 'Văn']]);

        // Lấy danh sách nhà xuất bản
        $publishers = $this->publisherService->getAllPublishers($request);

        // Kiểm tra số lượng nhà xuất bản
        $this->assertEquals(1, $publishers->count());
        $this->assertEquals('NXB Văn Học', $publishers->first()->name);
    }

    public function test_it_can_sort_publishers()
    {
        // Tạo 3 nhà xuất bản
        Publisher::factory()->create(['name' => 'A Publisher']);
        Publisher::factory()->create(['name' => 'B Publisher']);
        Publisher::factory()->create(['name' => 'C Publisher']);

        // Tạo request với sort theo name giảm dần
        $request = new Request(['sort' => '-name']);

        // Lấy danh sách nhà xuất bản
        $publishers = $this->publisherService->getAllPublishers($request);

        // Kiểm tra thứ tự sắp xếp
        $this->assertEquals('C Publisher', $publishers[0]->name);
        $this->assertEquals('B Publisher', $publishers[1]->name);
        $this->assertEquals('A Publisher', $publishers[2]->name);
    }

    public function test_it_can_create_publisher()
    {
        // Tạo PublisherDTO
        $publisherDTO = new PublisherDTO(
            name: 'NXB Văn Học',
            biography: 'Nhà xuất bản chuyên về sách văn học - nghệ thuật.'
        );

        // Tạo nhà xuất bản mới
        $publisher = $this->publisherService->createPublisher($publisherDTO);

        // Kiểm tra thông tin nhà xuất bản
        $this->assertEquals('NXB Văn Học', $publisher->name);
        $this->assertEquals('Nhà xuất bản chuyên về sách văn học - nghệ thuật.', $publisher->biography);

        // Kiểm tra trong database
        $this->assertDatabaseHas('publishers', [
            'name' => 'NXB Văn Học',
            'biography' => 'Nhà xuất bản chuyên về sách văn học - nghệ thuật.',
        ]);
    }

    public function test_it_can_restore_soft_deleted_publisher()
    {
        // Tạo nhà xuất bản
        $publisher = Publisher::factory()->create([
            'name' => 'NXB Khoa Học',
            'biography' => 'Nhà xuất bản chuyên về sách khoa học.',
        ]);

        // Xóa mềm nhà xuất bản
        $publisher->delete();
        $deletedAt = $publisher->deleted_at;

        // Tạo PublisherDTO với cùng tên nhưng biography khác
        $publisherDTO = new PublisherDTO(
            name: 'NXB Khoa Học',
            biography: 'Nhà xuất bản chuyên về sách khoa học và kỹ thuật.'
        );

        // Tạo nhà xuất bản (sẽ restore bản đã xóa)
        $restoredPublisher = $this->publisherService->createPublisher($publisherDTO);

        // Kiểm tra ID và thông tin đã được cập nhật
        $this->assertEquals($publisher->id, $restoredPublisher->id);
        $this->assertEquals('NXB Khoa Học', $restoredPublisher->name);
        $this->assertEquals('Nhà xuất bản chuyên về sách khoa học và kỹ thuật.', $restoredPublisher->biography);

        // Kiểm tra trạng thái trong database
        $this->assertDatabaseHas('publishers', [
            'id' => $publisher->id,
            'name' => 'NXB Khoa Học',
            'biography' => 'Nhà xuất bản chuyên về sách khoa học và kỹ thuật.',
        ]);

        // Kiểm tra không còn deleted_at
        $this->assertNull($restoredPublisher->deleted_at);
        $this->assertNotEquals($deletedAt, $restoredPublisher->deleted_at);
    }

    public function test_it_can_get_publisher_by_id()
    {
        // Tạo nhà xuất bản
        $publisher = Publisher::factory()->create([
            'name' => 'NXB Trẻ',
            'biography' => 'Nhà xuất bản dành cho trẻ em.',
        ]);

        // Lấy thông tin nhà xuất bản
        $foundPublisher = $this->publisherService->getPublisherById($publisher->id);

        // Kiểm tra thông tin nhà xuất bản
        $this->assertEquals($publisher->id, $foundPublisher->id);
        $this->assertEquals('NXB Trẻ', $foundPublisher->name);
        $this->assertEquals('Nhà xuất bản dành cho trẻ em.', $foundPublisher->biography);
    }

    public function test_it_throws_exception_when_publisher_not_found()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->publisherService->getPublisherById(999999);
    }

    public function test_it_can_update_publisher()
    {
        // Tạo nhà xuất bản
        $publisher = Publisher::factory()->create([
            'name' => 'NXB Trẻ',
            'biography' => 'Nhà xuất bản dành cho trẻ em.',
        ]);

        // Tạo PublisherDTO với thông tin cập nhật
        $publisherDTO = new PublisherDTO(
            name: 'NXB Trẻ (Đã cập nhật)',
            biography: 'Nhà xuất bản dành cho trẻ em và thanh thiếu niên.'
        );

        // Cập nhật nhà xuất bản
        $updatedPublisher = $this->publisherService->updatePublisher($publisher->id, $publisherDTO);

        // Kiểm tra thông tin nhà xuất bản
        $this->assertEquals($publisher->id, $updatedPublisher->id);
        $this->assertEquals('NXB Trẻ (Đã cập nhật)', $updatedPublisher->name);
        $this->assertEquals('Nhà xuất bản dành cho trẻ em và thanh thiếu niên.', $updatedPublisher->biography);

        // Kiểm tra trong database
        $this->assertDatabaseHas('publishers', [
            'id' => $publisher->id,
            'name' => 'NXB Trẻ (Đã cập nhật)',
            'biography' => 'Nhà xuất bản dành cho trẻ em và thanh thiếu niên.',
        ]);
    }

    public function test_it_can_delete_publisher()
    {
        // Tạo nhà xuất bản
        $publisher = Publisher::factory()->create();
        $publisherId = $publisher->id;

        // Xóa nhà xuất bản
        $this->publisherService->deletePublisher($publisher->id);

        // Kiểm tra nhà xuất bản đã bị xóa mềm
        $this->assertSoftDeleted('publishers', [
            'id' => $publisherId,
        ]);
    }

    public function test_it_throws_exception_when_deleting_non_existent_publisher()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->publisherService->deletePublisher(999999);
    }
}
