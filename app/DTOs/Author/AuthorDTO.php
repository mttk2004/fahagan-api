<?php

namespace App\DTOs\Author;

class AuthorDTO extends \App\DTOs\BaseDTO
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?string $biography,
        public readonly ?string $image_url,
        public readonly array $book_ids = [],
    ) {
    }

    public static function fromRequest(array $validatedData): self
    {
        $attributes = $validatedData['data']['attributes'] ?? [];
        $relationships = $validatedData['data']['relationships'] ?? [];

        // Lấy book_ids từ relationships nếu có
        $book_ids = [];
        if (isset($relationships['books']['data'])) {
            $book_ids = collect($relationships['books']['data'])->pluck('id')->toArray();
        }

        return new self(
            name: $attributes['name'] ?? null,
            biography: $attributes['biography'] ?? null,
            image_url: $attributes['image_url'] ?? null,
            book_ids: $book_ids,
        );
    }

    public function toArray(): array
    {
        $data = [];

        if ($this->name !== null) {
            $data['name'] = $this->name;
        }

        if ($this->biography !== null) {
            $data['biography'] = $this->biography;
        }

        if ($this->image_url !== null) {
            $data['image_url'] = $this->image_url;
        }

        return $data;
    }
}
