<?php

namespace Tests\Feature\Api\V1\Publisher;

use App\DTOs\Publisher\PublisherDTO;
use Tests\TestCase;

class PublisherDTOTest extends TestCase
{
    public function test_it_creates_publisher_dto_from_request()
    {
        $validatedData = [
            'data' => [
                'attributes' => [
                    'name' => 'NXB Văn Học',
                    'biography' => 'Nhà xuất bản chuyên về sách văn học - nghệ thuật.',
                ]
            ]
        ];

        $publisherDTO = PublisherDTO::fromRequest($validatedData);

        $this->assertEquals('NXB Văn Học', $publisherDTO->name);
        $this->assertEquals('Nhà xuất bản chuyên về sách văn học - nghệ thuật.', $publisherDTO->biography);
    }

    public function test_it_creates_publisher_dto_with_nullable_properties()
    {
        $validatedData = [
            'data' => [
                'attributes' => [
                    'name' => 'NXB Văn Học',
                    // Không có trường biography
                ]
            ]
        ];

        $publisherDTO = PublisherDTO::fromRequest($validatedData);

        $this->assertEquals('NXB Văn Học', $publisherDTO->name);
        $this->assertNull($publisherDTO->biography);
    }

    public function test_it_handles_empty_attributes()
    {
        $validatedData = [
            'data' => [
                // Không có attributes
            ]
        ];

        $publisherDTO = PublisherDTO::fromRequest($validatedData);

        $this->assertNull($publisherDTO->name);
        $this->assertNull($publisherDTO->biography);
    }

    public function test_it_converts_to_array()
    {
        $publisherDTO = new PublisherDTO(
            name: 'NXB Văn Học',
            biography: 'Nhà xuất bản chuyên về sách văn học - nghệ thuật.'
        );

        $array = $publisherDTO->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('NXB Văn Học', $array['name']);
        $this->assertEquals('Nhà xuất bản chuyên về sách văn học - nghệ thuật.', $array['biography']);
    }

    public function test_it_omits_null_properties_in_array()
    {
        $publisherDTO = new PublisherDTO(
            name: 'NXB Văn Học',
            biography: null
        );

        $array = $publisherDTO->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayNotHasKey('biography', $array);
    }
}
