<?php

namespace Tests\Feature\Api\V1\User;

use App\DTOs\User\UserDTO;
use Tests\TestCase;

class UserDTOTest extends TestCase
{
    public function test_it_creates_user_dto_from_request()
    {
        $validatedData = [
          'first_name' => 'John',
          'last_name' => 'Doe',
          'email' => 'john.doe@example.com',
          'phone' => '0987654321',
          'is_customer' => true,
        ];

        $userDTO = UserDTO::fromRequest($validatedData);

        $this->assertEquals('John', $userDTO->first_name);
        $this->assertEquals('Doe', $userDTO->last_name);
        $this->assertEquals('john.doe@example.com', $userDTO->email);
        $this->assertEquals('0987654321', $userDTO->phone);
        $this->assertEquals(true, $userDTO->is_customer);
        $this->assertNull($userDTO->password);
    }

    public function test_it_creates_user_dto_with_password()
    {
        $validatedData = [
          'first_name' => 'Jane',
          'last_name' => 'Doe',
          'email' => 'jane.doe@example.com',
          'phone' => '0123456789',
          'password' => 'password123',
          'is_customer' => false,
        ];

        $userDTO = UserDTO::fromRequest($validatedData);

        $this->assertEquals('Jane', $userDTO->first_name);
        $this->assertEquals('Doe', $userDTO->last_name);
        $this->assertEquals('jane.doe@example.com', $userDTO->email);
        $this->assertEquals('0123456789', $userDTO->phone);
        $this->assertEquals(false, $userDTO->is_customer);
        // Kiểm tra mật khẩu đã được mã hóa
        $this->assertNotNull($userDTO->password);
        $this->assertNotEquals('password123', $userDTO->password);
    }

    public function test_it_creates_user_dto_with_nullable_properties()
    {
        $validatedData = [
          'first_name' => 'Alex',
        ];

        $userDTO = UserDTO::fromRequest($validatedData);

        $this->assertEquals('Alex', $userDTO->first_name);
        $this->assertNull($userDTO->last_name);
        $this->assertNull($userDTO->email);
        $this->assertNull($userDTO->phone);
        $this->assertNull($userDTO->password);
        $this->assertTrue($userDTO->is_customer);
    }

    public function test_it_converts_to_array()
    {
        $userDTO = new UserDTO(
            first_name: 'John',
            last_name: 'Doe',
            email: 'john.doe@example.com',
            phone: '0987654321',
            password: 'hashed_password',
            is_customer: true
        );

        $array = $userDTO->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('John', $array['first_name']);
        $this->assertEquals('Doe', $array['last_name']);
        $this->assertEquals('john.doe@example.com', $array['email']);
        $this->assertEquals('0987654321', $array['phone']);
        $this->assertEquals('hashed_password', $array['password']);
        $this->assertEquals(true, $array['is_customer']);
    }

    public function test_it_omits_null_properties_in_array()
    {
        $userDTO = new UserDTO(
            first_name: 'John',
            last_name: null,
            email: null,
            phone: null,
            password: null,
            is_customer: null
        );

        $array = $userDTO->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('first_name', $array);
        $this->assertArrayNotHasKey('last_name', $array);
        $this->assertArrayNotHasKey('email', $array);
        $this->assertArrayNotHasKey('phone', $array);
        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('is_customer', $array);
    }
}
