<?php

namespace App\DTOs;

abstract class BaseDTO
{
  abstract public static function fromRequest(array $validatedData): self;
  abstract public function toArray(): array;

  /**
   * Filter out null values from array
   */
  protected function filterNullValues(array $data): array
  {
    return array_filter($data, fn($value) => $value !== null);
  }
}
