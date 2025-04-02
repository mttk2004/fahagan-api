<?php

namespace App\DTOs\Book;

class BookDTO
{
    public function __construct(
        public readonly string $title,
        public readonly string $description,
        public readonly float $price,
        public readonly int $edition,
        public readonly int $pages,
        public readonly ?string $image_url,
        public readonly string $publication_date,
        public readonly int $publisher_id,
        public readonly array $author_ids = [],
        public readonly array $genre_ids = [],
        public readonly int $sold_count = 0,
    ) {
    }

    public static function fromRequest(array $validatedData): self
    {
        $attributes = $validatedData['data']['attributes'];
        $relationships = $validatedData['data']['relationships'] ?? [];

        // Lấy author_ids từ relationships nếu có
        $author_ids = [];
        if (isset($relationships['authors']['data'])) {
            $author_ids = collect($relationships['authors']['data'])->pluck('id')->toArray();
        }

        // Lấy genre_ids từ relationships nếu có
        $genre_ids = [];
        if (isset($relationships['genres']['data'])) {
            $genre_ids = collect($relationships['genres']['data'])->pluck('id')->toArray();
        }

        // Lấy publisher_id từ relationships
        $publisher_id = $relationships['publisher']['id'] ?? null;

        return new self(
            title: $attributes['title'],
            description: $attributes['description'],
            price: $attributes['price'],
            edition: $attributes['edition'],
            pages: $attributes['pages'],
            image_url: $attributes['image_url'] ?? null,
            publication_date: $attributes['publication_date'],
            publisher_id: $publisher_id,
            author_ids: $author_ids,
            genre_ids: $genre_ids,
            sold_count: $attributes['sold_count'] ?? 0,
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'edition' => $this->edition,
            'pages' => $this->pages,
            'image_url' => $this->image_url,
            'publication_date' => $this->publication_date,
            'publisher_id' => $this->publisher_id,
            'sold_count' => $this->sold_count,
        ];
    }
}
