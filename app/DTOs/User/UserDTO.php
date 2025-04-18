<?php

namespace App\DTOs\User;

class UserDTO extends \App\DTOs\BaseDTO
{
  public function __construct(
    public readonly ?string $first_name,
    public readonly ?string $last_name,
    public readonly ?string $email,
    public readonly ?string $phone,
    public readonly ?string $password,
    public readonly ?bool $is_customer = true,
  ) {}

  public static function fromRequest(array $validatedData): self
  {
    return new self(
      first_name: $validatedData['first_name'] ?? null,
      last_name: $validatedData['last_name'] ?? null,
      email: $validatedData['email'] ?? null,
      phone: $validatedData['phone'] ?? null,
      password: isset($validatedData['password']) ? bcrypt($validatedData['password']) : null,
      is_customer: $validatedData['is_customer'] ?? true,
    );
  }

  public function toArray(): array
  {
    $data = [];

    if ($this->first_name !== null) {
      $data['first_name'] = $this->first_name;
    }

    if ($this->last_name !== null) {
      $data['last_name'] = $this->last_name;
    }

    if ($this->email !== null) {
      $data['email'] = $this->email;
    }

    if ($this->phone !== null) {
      $data['phone'] = $this->phone;
    }

    if ($this->password !== null) {
      $data['password'] = $this->password;
    }

    if ($this->is_customer !== null) {
      $data['is_customer'] = $this->is_customer;
    }

    return $data;
  }
}
