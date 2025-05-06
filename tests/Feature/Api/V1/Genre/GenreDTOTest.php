<?php

namespace Tests\Feature\Api\V1\Genre;

use App\DTOs\GenreDTO;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class GenreDTOTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_be_constructed_with_values()
    {
        // Tạo DTO với giá trị
        $dto = new GenreDTO(
            name: 'Tiểu thuyết lãng mạn',
            slug: 'tieu-thuyet-lang-man',
            description: 'Mô tả về thể loại tiểu thuyết lãng mạn'
        );

        // Kiểm tra các giá trị đã được gán đúng
        $this->assertEquals('Tiểu thuyết lãng mạn', $dto->name);
        $this->assertEquals('tieu-thuyet-lang-man', $dto->slug);
        $this->assertEquals('Mô tả về thể loại tiểu thuyết lãng mạn', $dto->description);
    }

    public function test_it_can_be_constructed_with_nullable_values()
    {
        // Tạo DTO với một số giá trị null
        $dto = new GenreDTO(
            name: 'Tiểu thuyết',
            slug: 'tieu-thuyet',
            description: null
        );

        // Kiểm tra các giá trị đã được gán đúng
        $this->assertEquals('Tiểu thuyết', $dto->name);
        $this->assertEquals('tieu-thuyet', $dto->slug);
        $this->assertNull($dto->description);
    }

    public function test_it_can_be_constructed_with_minimal_values()
    {
        // Tạo DTO chỉ với giá trị name và các giá trị khác là null
        $dto = new GenreDTO(
            name: 'Tiểu thuyết kiếm hiệp',
            slug: null,
            description: null
        );

        // Kiểm tra các giá trị đã được gán đúng
        $this->assertEquals('Tiểu thuyết kiếm hiệp', $dto->name);
        $this->assertNull($dto->slug);
        $this->assertNull($dto->description);
    }

    public function test_it_can_convert_to_array()
    {
        // Tạo DTO với đầy đủ thông tin
        $dto = new GenreDTO(
            name: 'Tiểu thuyết',
            slug: 'tieu-thuyet',
            description: 'Mô tả về thể loại tiểu thuyết'
        );

        // Chuyển đổi thành mảng
        $array = $dto->toArray();

        // Kiểm tra mảng có đúng cấu trúc và giá trị
        $this->assertIsArray($array);
        $this->assertEquals('Tiểu thuyết', $array['name']);
        $this->assertEquals('tieu-thuyet', $array['slug']);
        $this->assertEquals('Mô tả về thể loại tiểu thuyết', $array['description']);
    }

    public function test_it_can_convert_to_array_with_nullable_values()
    {
        // Tạo DTO với một số giá trị null
        $dto = new GenreDTO(
            name: 'Tiểu thuyết',
            slug: null,
            description: null
        );

        // Chuyển đổi thành mảng
        $array = $dto->toArray();

        // Kiểm tra mảng có đúng cấu trúc và giá trị
        $this->assertIsArray($array);
        $this->assertEquals('Tiểu thuyết', $array['name']);
        $this->assertArrayNotHasKey('slug', $array);
        $this->assertArrayNotHasKey('description', $array);
    }

    public function test_it_can_be_created_from_request_data()
    {
        // Giả lập dữ liệu validated từ request với định dạng trực tiếp
        $validatedData = [
            'name' => 'Tiểu thuyết lịch sử',
            'slug' => 'tieu-thuyet-lich-su',
            'description' => 'Thể loại tiểu thuyết lấy bối cảnh từ các sự kiện lịch sử.',
        ];

        // Tạo DTO từ dữ liệu request
        $dto = GenreDTO::fromRequest($validatedData);

        // Kiểm tra các giá trị đã được gán đúng
        $this->assertEquals('Tiểu thuyết lịch sử', $dto->name);
        $this->assertEquals('tieu-thuyet-lich-su', $dto->slug);
        $this->assertEquals('Thể loại tiểu thuyết lấy bối cảnh từ các sự kiện lịch sử.', $dto->description);
    }

    public function test_it_can_be_created_from_request_with_missing_optional_fields()
    {
        // Giả lập dữ liệu validated từ request chỉ có trường bắt buộc với định dạng trực tiếp
        $validatedData = [
            'name' => 'Tiểu thuyết trinh thám',
        ];

        // Tạo DTO từ dữ liệu request
        $dto = GenreDTO::fromRequest($validatedData);

        // Kiểm tra các giá trị đã được gán đúng
        $this->assertEquals('Tiểu thuyết trinh thám', $dto->name);
        $this->assertNull($dto->slug);
        $this->assertNull($dto->description);
    }
}
