<?php

namespace Tests\Feature\Api\V1\Genre;

use App\DTOs\Genre\GenreDTO;
use App\Models\Genre;
use App\Models\User;
use App\Services\GenreService;
use Database\Seeders\TestPermissionSeeder;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class GenreServiceTest extends TestCase
{
    use RefreshDatabase;

    private GenreService $genreService;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Chạy seeder để tạo các quyền cần thiết
        $this->seed(TestPermissionSeeder::class);

        // Tạo một người dùng admin và gán quyền quản lý thể loại
        $this->adminUser = User::factory()->create([
            'is_customer' => false,
        ]);
        $this->adminUser->givePermissionTo([
            'view_genres',
            'create_genres',
            'edit_genres',
            'delete_genres',
            'restore_genres',
        ]);

        // Khởi tạo GenreService
        $this->genreService = app(GenreService::class);
    }

    public function test_it_can_get_all_genres()
    {
        // Tạo 5 thể loại
        Genre::factory(5)->create();

        // Tạo mock request
        $request = new Request;

        // Gọi service để lấy tất cả thể loại
        $result = $this->genreService->getAllGenres($request);

        // Kiểm tra số lượng thể loại trả về
        $this->assertEquals(5, $result->total());
    }

    public function test_it_can_filter_genres_by_name()
    {
        // Tạo các thể loại với tên khác nhau
        Genre::factory()->create(['name' => 'Fantasy']);
        Genre::factory()->create(['name' => 'Science Fiction']);
        Genre::factory()->create(['name' => 'Romance']);

        // Tạo request với filter name
        $request = new Request(['filter' => ['name' => 'Fantasy']]);

        // Gọi service để lấy thể loại đã lọc
        $result = $this->genreService->getAllGenres($request);

        // Kiểm tra số lượng và tên thể loại
        $this->assertEquals(1, $result->total());
        $this->assertEquals('Fantasy', $result->first()->name);
    }

    public function test_it_can_sort_genres_by_name()
    {
        // Tạo các thể loại với tên khác nhau
        Genre::factory()->create(['name' => 'Comics']);
        Genre::factory()->create(['name' => 'Adventure']);
        Genre::factory()->create(['name' => 'Biography']);

        // Tạo request với sort theo tên
        $request = new Request(['sort' => ['name']]);

        // Gọi service để lấy thể loại đã sắp xếp
        $result = $this->genreService->getAllGenres($request);

        // Kiểm tra thứ tự các thể loại
        $genres = $result->items();
        $this->assertEquals('Adventure', $genres[0]->name);
        $this->assertEquals('Biography', $genres[1]->name);
        $this->assertEquals('Comics', $genres[2]->name);
    }

    public function test_it_can_create_genre()
    {
        // Tạo DTO để tạo thể loại mới
        $dto = new GenreDTO(
            name: 'Fantasy Fiction',
            slug: 'fantasy-fiction',
            description: 'A genre of fiction set in a fictional universe'
        );

        // Gọi service để tạo thể loại
        $genre = $this->genreService->createGenre($dto);

        // Kiểm tra thể loại đã được tạo
        $this->assertEquals('Fantasy Fiction', $genre->name);
        $this->assertEquals('fantasy-fiction', $genre->slug);
        $this->assertEquals('A genre of fiction set in a fictional universe', $genre->description);

        // Kiểm tra dữ liệu trong database
        $this->assertDatabaseHas('genres', [
            'name' => 'Fantasy Fiction',
            'slug' => 'fantasy-fiction',
        ]);
    }

    public function test_it_can_get_genre_by_id()
    {
        // Tạo một thể loại
        $genreCreated = Genre::factory()->create([
            'name' => 'Mystery',
            'slug' => 'mystery',
            'description' => 'Mystery novels',
        ]);

        // Gọi service để lấy thể loại theo ID
        $genre = $this->genreService->getGenreById($genreCreated->id);

        // Kiểm tra thông tin thể loại
        $this->assertEquals($genreCreated->id, $genre->id);
        $this->assertEquals('Mystery', $genre->name);
        $this->assertEquals('mystery', $genre->slug);
    }

    public function test_it_throws_exception_when_genre_not_found()
    {
        // Thiết lập kỳ vọng về exception
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        // Gọi service với ID không tồn tại
        $this->genreService->getGenreById(999);
    }

    public function test_it_can_get_genre_by_slug()
    {
        // Tạo một thể loại
        $genreCreated = Genre::factory()->create([
            'name' => 'Horror',
            'slug' => 'horror',
            'description' => 'Horror stories',
        ]);

        // Gọi service để lấy thể loại theo slug
        $genre = $this->genreService->getGenreBySlug('horror');

        // Kiểm tra thông tin thể loại
        $this->assertEquals($genreCreated->id, $genre->id);
        $this->assertEquals('Horror', $genre->name);
        $this->assertEquals('horror', $genre->slug);
    }

    public function test_it_throws_exception_when_genre_slug_not_found()
    {
        // Thiết lập kỳ vọng về exception
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        // Gọi service với slug không tồn tại
        $this->genreService->getGenreBySlug('non-existent-slug');
    }

    public function test_it_can_update_genre()
    {
        // Tạo một thể loại
        $genreCreated = Genre::factory()->create([
            'name' => 'Old Name',
            'slug' => 'old-slug',
            'description' => 'Old description',
        ]);

        // Tạo DTO với thông tin cập nhật
        $dto = new GenreDTO(
            name: 'New Name',
            slug: 'new-slug',
            description: 'New description'
        );

        // Gọi service để cập nhật thể loại
        $updatedGenre = $this->genreService->updateGenre($genreCreated->id, $dto);

        // Kiểm tra thông tin đã cập nhật
        $this->assertEquals($genreCreated->id, $updatedGenre->id);
        $this->assertEquals('New Name', $updatedGenre->name);
        $this->assertEquals('new-slug', $updatedGenre->slug);
        $this->assertEquals('New description', $updatedGenre->description);

        // Kiểm tra dữ liệu trong database
        $this->assertDatabaseHas('genres', [
            'id' => $genreCreated->id,
            'name' => 'New Name',
            'slug' => 'new-slug',
            'description' => 'New description',
        ]);
    }

    public function test_it_can_update_partial_genre_info()
    {
        // Tạo một thể loại
        $genreCreated = Genre::factory()->create([
            'name' => 'Original Name',
            'slug' => 'original-slug',
            'description' => 'Original description',
        ]);

        // Tạo DTO chỉ cập nhật tên
        $dto = new GenreDTO(
            name: 'Updated Name',
            slug: null,
            description: null
        );

        // Gọi service để cập nhật thể loại
        $updatedGenre = $this->genreService->updateGenre($genreCreated->id, $dto);

        // Kiểm tra chỉ tên đã được cập nhật, các trường khác không thay đổi
        $this->assertEquals($genreCreated->id, $updatedGenre->id);
        $this->assertEquals('Updated Name', $updatedGenre->name);
        $this->assertEquals('updated-name', $updatedGenre->slug); // Tự động cập nhật slug khi name thay đổi
        $this->assertEquals('Original description', $updatedGenre->description); // Không đổi
    }

    public function test_it_can_delete_genre()
    {
        // Tạo một thể loại
        $genre = Genre::factory()->create();

        // Gọi service để xóa thể loại
        $this->genreService->deleteGenre($genre->id);

        // Kiểm tra thể loại đã bị xóa mềm
        $this->assertSoftDeleted('genres', [
            'id' => $genre->id,
        ]);
    }

    public function test_it_can_restore_genre()
    {
        // Tạo một thể loại
        $genre = Genre::factory()->create();

        // Xóa mềm thể loại
        $genre->delete();

        // Kiểm tra thể loại đã bị xóa mềm
        $this->assertSoftDeleted('genres', ['id' => $genre->id]);

        // Gọi service để khôi phục thể loại
        $restoredGenre = $this->genreService->restoreGenre($genre->id);

        // Kiểm tra thể loại đã được khôi phục
        $this->assertNull($restoredGenre->deleted_at);
        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'deleted_at' => null,
        ]);
    }

    public function test_it_throws_exception_when_restoring_non_deleted_genre()
    {
        // Tạo một thể loại (chưa bị xóa)
        $genre = Genre::factory()->create();

        // Thiết lập kỳ vọng về exception
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Thể loại này chưa bị xóa.');

        // Gọi service để khôi phục thể loại chưa bị xóa
        $this->genreService->restoreGenre($genre->id);
    }
}
