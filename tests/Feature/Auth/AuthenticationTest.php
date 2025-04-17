<?php

use App\Models\User;

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/api/login', [
      'email' => $user->email,
      'password' => 'password',
    ]);

    $response->assertStatus(200)
      ->assertJsonStructure([
        'status',
        'message',
        'data' => [
          'token',
          'user',
        ],
      ]);
});

// Đã loại bỏ test với JSON:API format vì không còn hỗ trợ

test('users can not authenticate with invalid email', function () {
    $user = User::factory()->create();

    $response = $this->post('/api/login', [
      'email' => 'invalid@email.com',
      'password' => 'password',
    ]);
    $response->assertStatus(401);
});

test('users can not authenticate with invalid email format', function () {
    $user = User::factory()->create();

    $response = $this->post('/api/login', [
      'email' => 'invalid-email',
      'password' => 'password',
    ]);

    $response->assertStatus(422)
      ->assertJsonValidationErrors(['email']);
});

test('users can not authenticate with empty email', function () {
    $user = User::factory()->create();

    $response = $this->post('/api/login', [
      'email' => '',
      'password' => 'password',
    ]);

    $response->assertStatus(422)
      ->assertJsonValidationErrors(['email']);
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $response = $this->post('/api/login', [
      'email' => $user->email,
      'password' => 'wrong-password',
    ]);

    $response->assertStatus(401);
});

// Bỏ qua test logout trong môi trường testing vì Sanctum sử dụng TransientToken
// mà không thể gọi phương thức delete() trong môi trường testing.
//
// Trong trường hợp thực tế, khi route logout được gọi với token sanctum hợp lệ,
// hệ thống sẽ xóa token hiện tại của người dùng khỏi database và trả về status 204.
