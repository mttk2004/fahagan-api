<?php

namespace App\DTOs\Genre;

class GenreDTO extends \App\DTOs\BaseDTO
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?string $slug,
        public readonly ?string $description,
    ) {
    }

    public static function fromRequest(array $validatedData): self
    {
        $attributes = $validatedData['data']['attributes'] ?? [];

        return new self(
            name: $attributes['name'] ?? null,
            slug: $attributes['slug'] ?? null,
            description: $attributes['description'] ?? null,
        );
    }

    public function toArray(): array
    {
        $data = [];

        if ($this->name !== null) {
            $data['name'] = $this->name;
        }

        if ($this->slug !== null) {
            $data['slug'] = $this->slug;
        }

        if ($this->description !== null) {
            $data['description'] = $this->description;
        }

        return $data;
    }
}
