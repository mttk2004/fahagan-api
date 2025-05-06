<?php

namespace App\DTOs\Order;

class OrderDTO extends \App\DTOs\BaseDTO
{
    public function __construct(
        public readonly ?string $method,
        public readonly ?int $address_id = null,
        public readonly array $items = [],
    ) {
    }

    public static function fromRequest(array $validatedData): self
    {
        $attributes = $validatedData['data']['attributes'] ?? [];
        $relationships = $validatedData['data']['relationships'] ?? [];
        $items = $relationships['items'] ?? [];

        $items = array_map(
            fn ($item) => [
                'id' => $item['id'],
            ],
            $items
        );

        $address_id = isset($relationships['address']['id']) ? $relationships['address']['id'] : null;

        return new self(
            method: $attributes['method'] ?? null,
            address_id: $address_id,
            items: $items
        );
    }

    public function toArray(): array
    {
        $data = [];

        if ($this->method !== null) {
            $data['method'] = $this->method;
        }

        if ($this->address_id !== null) {
            $data['address_id'] = $this->address_id;
        }

        if (! empty($this->items)) {
            $data['items'] = $this->items;
        }

        return $data;
    }
}
