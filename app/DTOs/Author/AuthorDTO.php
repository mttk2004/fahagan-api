<?php

namespace App\DTOs\Author;

class AuthorDTO extends \App\DTOs\BaseDTO
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?string $biography,
        public readonly ?string $image_url,
    ) {}

    public static function fromRequest(array $validatedData): self
    {
        return new self(
            name: $validatedData['name'] ?? null,
            biography: $validatedData['biography'] ?? null,
            image_url: $validatedData['image_url'] ?? null,
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
