<?php

namespace App\DTOs\Book;

class BookDTO extends \App\DTOs\BaseDTO
{
  public function __construct(
    public ?string $title,
    public ?string $description,
    public ?float $price,
    public ?int $edition,
    public ?int $pages,
    public ?string $image_url,
    public ?string $publication_date,
    public ?int $publisher_id,
    public array $author_ids = [],
    public array $genre_ids = [],
    public ?int $sold_count = 0,
    public ?int $available_count = 0,
  ) {}

  public static function fromRequest(array $validatedData): self
  {
    $attributes = $validatedData['data']['attributes'] ?? [];
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

    // Lấy publisher_id từ relationships nếu có
    $publisher_id = null;
    if (isset($relationships['publisher']['id'])) {
      $publisher_id = $relationships['publisher']['id'];
    }

    return new self(
      title: $attributes['title'] ?? null,
      description: $attributes['description'] ?? null,
      price: isset($attributes['price']) ? (float)$attributes['price'] : null,
      edition: isset($attributes['edition']) ? (int)$attributes['edition'] : null,
      pages: isset($attributes['pages']) ? (int)$attributes['pages'] : null,
      image_url: $attributes['image_url'] ?? null,
      publication_date: $attributes['publication_date'] ?? null,
      publisher_id: $publisher_id,
      author_ids: $author_ids,
      genre_ids: $genre_ids,
      sold_count: isset($attributes['sold_count']) ? (int)$attributes['sold_count'] : 0,
      available_count: isset($attributes['available_count']) ? (int)$attributes['available_count'] : 0,
    );
  }

  public function toArray(): array
  {
    $data = [];

    if ($this->title !== null) {
      $data['title'] = $this->title;
    }

    if ($this->description !== null) {
      $data['description'] = $this->description;
    }

    if ($this->price !== null) {
      $data['price'] = $this->price;
    }

    if ($this->edition !== null) {
      $data['edition'] = $this->edition;
    }

    if ($this->pages !== null) {
      $data['pages'] = $this->pages;
    }

    if ($this->image_url !== null) {
      $data['image_url'] = $this->image_url;
    }

    if ($this->publication_date !== null) {
      $data['publication_date'] = $this->publication_date;
    }

    if ($this->publisher_id !== null) {
      $data['publisher_id'] = $this->publisher_id;
    }

    if ($this->sold_count !== null) {
      $data['sold_count'] = $this->sold_count;
    }

    if ($this->available_count !== null) {
      $data['available_count'] = $this->available_count;
    }

    return $data;
  }
}
