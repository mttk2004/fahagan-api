<?php

namespace App\DTOs;

class PublisherDTO extends BaseDTO
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?string $biography,
    ) {
    }

    public static function fromRequest(array $validatedData): self
    {
        return new self(
            name: $validatedData['name'] ?? null,
            biography: $validatedData['biography'] ?? null,
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

        return $data;
    }
}
