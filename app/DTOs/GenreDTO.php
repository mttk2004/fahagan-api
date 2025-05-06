<?php

namespace App\DTOs;

class GenreDTO extends BaseDTO
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?string $slug,
        public readonly ?string $description,
    ) {
    }

    public static function fromRequest(array $validatedData): self
    {
        return new self(
            name: $validatedData['name'] ?? null,
            slug: $validatedData['slug'] ?? null,
            description: $validatedData['description'] ?? null,
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
