<?php

namespace Tests\Feature\Api\V1\Book;

use App\DTOs\Book\BookDTO;
use PHPUnit\Framework\TestCase;

class BookDTOTest extends TestCase
{
    public function test_it_can_create_book_dto_with_all_properties()
    {
        // Tạo BookDTO với tất cả thuộc tính
        $bookDTO = new BookDTO(
            'Sách Test',
            'Mô tả sách test',
            150000,
            1,
            200,
            'https://example.com/book.jpg',
            '2023-01-01',
            1,
            [1, 2, 3],
            [4, 5, 6],
            10,
            31
        );

        // Kiểm tra các thuộc tính
        $this->assertEquals('Sách Test', $bookDTO->title);
        $this->assertEquals('Mô tả sách test', $bookDTO->description);
        $this->assertEquals(150000, $bookDTO->price);
        $this->assertEquals(1, $bookDTO->edition);
        $this->assertEquals(200, $bookDTO->pages);
        $this->assertEquals('https://example.com/book.jpg', $bookDTO->image_url);
        $this->assertEquals('2023-01-01', $bookDTO->publication_date);
        $this->assertEquals(1, $bookDTO->publisher_id);
        $this->assertEquals([1, 2, 3], $bookDTO->author_ids);
        $this->assertEquals([4, 5, 6], $bookDTO->genre_ids);
        $this->assertEquals(10, $bookDTO->sold_count);
    }

    public function test_it_can_create_book_dto_with_nullable_properties()
    {
        // Tạo BookDTO với một số thuộc tính null
        $bookDTO = new BookDTO(
            'Sách Test',
            'Mô tả sách test',
            null,
            null,
            null,
            null,
            null,
            null
        );

        // Kiểm tra các thuộc tính
        $this->assertEquals('Sách Test', $bookDTO->title);
        $this->assertEquals('Mô tả sách test', $bookDTO->description);
        $this->assertNull($bookDTO->price);
        $this->assertNull($bookDTO->edition);
        $this->assertNull($bookDTO->pages);
        $this->assertNull($bookDTO->image_url);
        $this->assertNull($bookDTO->publication_date);
        $this->assertNull($bookDTO->publisher_id);
        $this->assertEquals([], $bookDTO->author_ids);
        $this->assertEquals([], $bookDTO->genre_ids);
        $this->assertEquals(0, $bookDTO->sold_count);
    }

    public function test_it_can_create_from_request_data()
    {
        // Dữ liệu request giả lập
        $requestData = [
          'data' => [
            'attributes' => [
              'title' => 'Sách Test',
              'description' => 'Mô tả sách test',
              'price' => 150000,
              'edition' => 1,
              'pages' => 200,
              'image_url' => 'https://example.com/book.jpg',
              'publication_date' => '2023-01-01',
              'sold_count' => 10,
              'available_count' => 31,
            ],
            'relationships' => [
              'publisher' => [
                'id' => 1,
              ],
              'authors' => [
                ['id' => 1],
                ['id' => 2],
              ],
              'genres' => [
                ['id' => 3],
                ['id' => 4],
              ],
            ],
          ],
        ];

        // Tạo DTO từ request
        $bookDTO = BookDTO::fromRequest($requestData);

        // Kiểm tra các thuộc tính
        $this->assertEquals('Sách Test', $bookDTO->title);
        $this->assertEquals('Mô tả sách test', $bookDTO->description);
        $this->assertEquals(150000, $bookDTO->price);
        $this->assertEquals(1, $bookDTO->edition);
        $this->assertEquals(200, $bookDTO->pages);
        $this->assertEquals('https://example.com/book.jpg', $bookDTO->image_url);
        $this->assertEquals('2023-01-01', $bookDTO->publication_date);
        $this->assertEquals(1, $bookDTO->publisher_id);
        $this->assertEquals([1, 2], $bookDTO->author_ids);
        $this->assertEquals([3, 4], $bookDTO->genre_ids);
        $this->assertEquals(10, $bookDTO->sold_count);
        $this->assertEquals(31, $bookDTO->available_count);
    }

    public function test_it_can_convert_to_array()
    {
        // Tạo BookDTO
        $bookDTO = new BookDTO(
            'Sách Test',
            'Mô tả sách test',
            150000,
            1,
            200,
            'https://example.com/book.jpg',
            '2023-01-01',
            1
        );

        // Chuyển đổi sang array
        $array = $bookDTO->toArray();

        // Kiểm tra array
        $this->assertIsArray($array);
        $this->assertEquals('Sách Test', $array['title']);
        $this->assertEquals('Mô tả sách test', $array['description']);
        $this->assertEquals(150000, $array['price']);
        $this->assertEquals(1, $array['edition']);
        $this->assertEquals(200, $array['pages']);
        $this->assertEquals('https://example.com/book.jpg', $array['image_url']);
        $this->assertEquals('2023-01-01', $array['publication_date']);
        $this->assertEquals(1, $array['publisher_id']);
    }

    public function test_it_only_includes_non_null_values_in_array()
    {
        // Tạo BookDTO với một số thuộc tính null
        $bookDTO = new BookDTO(
            'Sách Test',
            'Mô tả sách test',
            null,
            null,
            null,
            null,
            null,
            null
        );

        // Chuyển đổi sang array
        $array = $bookDTO->toArray();

        // Kiểm tra array chỉ chứa các thuộc tính không null
        $this->assertIsArray($array);
        $this->assertEquals('Sách Test', $array['title']);
        $this->assertEquals('Mô tả sách test', $array['description']);
        $this->assertArrayNotHasKey('price', $array);
        $this->assertArrayNotHasKey('edition', $array);
        $this->assertArrayNotHasKey('pages', $array);
        $this->assertArrayNotHasKey('image_url', $array);
        $this->assertArrayNotHasKey('publication_date', $array);
        $this->assertArrayNotHasKey('publisher_id', $array);
    }

    public function test_it_handles_missing_relationships_section()
    {
        // Dữ liệu request không có phần relationships
        $requestData = [
          'data' => [
            'attributes' => [
              'title' => 'Sách Test',
              'description' => 'Mô tả sách test',
              'price' => 150000,
              'edition' => 1,
              'pages' => 200,
            ],
          ],
        ];

        // Tạo DTO từ request
        $bookDTO = BookDTO::fromRequest($requestData);

        // Kiểm tra các thuộc tính
        $this->assertEquals('Sách Test', $bookDTO->title);
        $this->assertEquals('Mô tả sách test', $bookDTO->description);
        $this->assertEquals(150000, $bookDTO->price);
        $this->assertEquals(1, $bookDTO->edition);
        $this->assertEquals(200, $bookDTO->pages);
        $this->assertNull($bookDTO->publisher_id);
        $this->assertEmpty($bookDTO->author_ids);
        $this->assertEmpty($bookDTO->genre_ids);
    }

    public function test_it_handles_partial_relationships_data()
    {
        // Dữ liệu request với một phần relationships
        $requestData = [
          'data' => [
            'attributes' => [
              'title' => 'Sách Test',
            ],
            'relationships' => [
              'authors' => [
                ['id' => 1],
                ['id' => 2],
              ],
              // Thiếu genres và publisher
            ],
          ],
        ];

        // Tạo DTO từ request
        $bookDTO = BookDTO::fromRequest($requestData);

        // Kiểm tra các thuộc tính
        $this->assertEquals('Sách Test', $bookDTO->title);
        $this->assertEquals([1, 2], $bookDTO->author_ids);
        $this->assertEmpty($bookDTO->genre_ids);
        $this->assertNull($bookDTO->publisher_id);
    }

    public function test_it_handles_empty_relationships_data()
    {
        // Dữ liệu request với relationships rỗng
        $requestData = [
          'data' => [
            'attributes' => [
              'title' => 'Sách Test',
            ],
            'relationships' => [
              'authors' => [],
              'genres' => [],
            ],
          ],
        ];

        // Tạo DTO từ request
        $bookDTO = BookDTO::fromRequest($requestData);

        // Kiểm tra các thuộc tính
        $this->assertEquals('Sách Test', $bookDTO->title);
        $this->assertEmpty($bookDTO->author_ids);
        $this->assertEmpty($bookDTO->genre_ids);
    }

    public function test_it_handles_invalid_publisher_format()
    {
        // Dữ liệu request với publisher format không đúng
        $requestData = [
          'data' => [
            'attributes' => [
              'title' => 'Sách Test',
            ],
            'relationships' => [
              'publisher' => [
                // Thiếu 'id' field
                'name' => 'Nhà xuất bản test',
              ],
            ],
          ],
        ];

        // Tạo DTO từ request
        $bookDTO = BookDTO::fromRequest($requestData);

        // Kiểm tra các thuộc tính
        $this->assertEquals('Sách Test', $bookDTO->title);
        $this->assertNull($bookDTO->publisher_id);
    }

    public function test_it_handles_missing_attributes_section()
    {
        // Dữ liệu request không có phần attributes
        $requestData = [
          'data' => [
            'relationships' => [
              'publisher' => [
                'id' => 1,
              ],
            ],
          ],
        ];

        // Tạo DTO từ request
        $bookDTO = BookDTO::fromRequest($requestData);

        // Kiểm tra các thuộc tính
        $this->assertNull($bookDTO->title);
        $this->assertEquals(1, $bookDTO->publisher_id);
    }
}
