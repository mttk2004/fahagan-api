<?php

namespace Tests\Feature\Api\V1\Author;

use App\DTOs\Author\AuthorDTO;
use Tests\TestCase;

class AuthorDTOTest extends TestCase
{
    public function test_it_creates_author_dto_from_json_api_request()
    {
        $validatedData = [
          'data' => [
            'attributes' => [
              'name' => 'Nguyễn Nhật Ánh',
              'biography' => 'Tác giả nổi tiếng với nhiều tác phẩm văn học thiếu nhi và thanh thiếu niên.',
              'image_url' => 'https://example.com/authors/nguyen-nhat-anh.jpg',
            ],
          ],
        ];

        $authorDTO = AuthorDTO::fromRequest($validatedData);

        $this->assertEquals('Nguyễn Nhật Ánh', $authorDTO->name);
        $this->assertEquals('Tác giả nổi tiếng với nhiều tác phẩm văn học thiếu nhi và thanh thiếu niên.', $authorDTO->biography);
        $this->assertEquals('https://example.com/authors/nguyen-nhat-anh.jpg', $authorDTO->image_url);
        $this->assertEmpty($authorDTO->book_ids);
    }

    public function test_it_creates_author_dto_with_book_ids()
    {
        $validatedData = [
          'data' => [
            'attributes' => [
              'name' => 'Nguyễn Nhật Ánh',
              'biography' => 'Tác giả nổi tiếng với nhiều tác phẩm văn học thiếu nhi và thanh thiếu niên.',
              'image_url' => 'https://example.com/authors/nguyen-nhat-anh.jpg',
            ],
            'relationships' => [
              'books' => [
                'data' => [
                  ['id' => 1, 'type' => 'books'],
                  ['id' => 2, 'type' => 'books'],
                ],
              ],
            ],
          ],
        ];

        $authorDTO = AuthorDTO::fromRequest($validatedData);

        $this->assertEquals('Nguyễn Nhật Ánh', $authorDTO->name);
        $this->assertEquals([1, 2], $authorDTO->book_ids);
    }

    public function test_it_creates_author_dto_with_nullable_properties()
    {
        $validatedData = [
          'data' => [
            'attributes' => [
              'name' => 'Nguyễn Nhật Ánh',
            ],
          ],
        ];

        $authorDTO = AuthorDTO::fromRequest($validatedData);

        $this->assertEquals('Nguyễn Nhật Ánh', $authorDTO->name);
        $this->assertNull($authorDTO->biography);
        $this->assertNull($authorDTO->image_url);
        $this->assertEmpty($authorDTO->book_ids);
    }

    public function test_it_creates_author_dto_with_empty_relationships()
    {
        $validatedData = [
          'data' => [
            'attributes' => [
              'name' => 'Nguyễn Nhật Ánh',
              'biography' => 'Tác giả nổi tiếng.',
              'image_url' => 'https://example.com/nguyen-nhat-anh.jpg',
            ],
            'relationships' => [
              'books' => [
                'data' => [],
              ],
            ],
          ],
        ];

        $authorDTO = AuthorDTO::fromRequest($validatedData);

        $this->assertEquals('Nguyễn Nhật Ánh', $authorDTO->name);
        $this->assertEquals('Tác giả nổi tiếng.', $authorDTO->biography);
        $this->assertEquals('https://example.com/nguyen-nhat-anh.jpg', $authorDTO->image_url);
        $this->assertEmpty($authorDTO->book_ids);
    }

    public function test_it_converts_to_array()
    {
        $authorDTO = new AuthorDTO(
            name: 'Nguyễn Nhật Ánh',
            biography: 'Tác giả nổi tiếng với nhiều tác phẩm văn học thiếu nhi và thanh thiếu niên.',
            image_url: 'https://example.com/authors/nguyen-nhat-anh.jpg',
            book_ids: [1, 2, 3]
        );

        $array = $authorDTO->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('Nguyễn Nhật Ánh', $array['name']);
        $this->assertEquals('Tác giả nổi tiếng với nhiều tác phẩm văn học thiếu nhi và thanh thiếu niên.', $array['biography']);
        $this->assertEquals('https://example.com/authors/nguyen-nhat-anh.jpg', $array['image_url']);
        $this->assertArrayNotHasKey('book_ids', $array);
    }

    public function test_it_omits_null_properties_in_array()
    {
        $authorDTO = new AuthorDTO(
            name: 'Nguyễn Nhật Ánh',
            biography: null,
            image_url: null,
            book_ids: []
        );

        $array = $authorDTO->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayNotHasKey('biography', $array);
        $this->assertArrayNotHasKey('image_url', $array);
        $this->assertArrayNotHasKey('book_ids', $array);
    }

    public function test_it_handles_missing_relationships_section()
    {
        $validatedData = [
          'data' => [
            'attributes' => [
              'name' => 'Nguyễn Nhật Ánh',
              'biography' => 'Tác giả nổi tiếng.',
              'image_url' => 'https://example.com/nguyen-nhat-anh.jpg',
            ],
            // Không có phần relationships
          ],
        ];

        $authorDTO = AuthorDTO::fromRequest($validatedData);

        $this->assertEquals('Nguyễn Nhật Ánh', $authorDTO->name);
        $this->assertEquals('Tác giả nổi tiếng.', $authorDTO->biography);
        $this->assertEquals('https://example.com/nguyen-nhat-anh.jpg', $authorDTO->image_url);
        $this->assertEmpty($authorDTO->book_ids);
    }

    public function test_it_handles_invalid_book_ids_format_gracefully()
    {
        $validatedData = [
          'data' => [
            'attributes' => [
              'name' => 'Nguyễn Nhật Ánh',
            ],
            'relationships' => [
              'books' => [
                // Thiếu 'data'
              ],
            ],
          ],
        ];

        $authorDTO = AuthorDTO::fromRequest($validatedData);

        $this->assertEquals('Nguyễn Nhật Ánh', $authorDTO->name);
        $this->assertEmpty($authorDTO->book_ids);
    }
}
