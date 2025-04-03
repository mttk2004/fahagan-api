<?php

namespace App\DTOs\Publisher;

class PublisherDTO
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?string $biography,
    ) {
    }

    public static function fromRequest(array $validatedData): self
    {
        $attributes = $validatedData['data']['attributes'] ?? [];

        return new self(
            name: $attributes['name'] ?? null,
            biography: $attributes['biography'] ?? null,
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
