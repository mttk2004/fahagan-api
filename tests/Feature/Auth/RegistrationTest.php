<?php

test('new users can register', function () {
  $response = $this->post('/api/register', [
    'first_name' => 'Nguyễn',
    'last_name' => 'Test',
    'email' => 'test@example.com',
    'phone' => '0987654321',
    'password' => 'password',
    'password_confirmation' => 'password',
  ]);

  $response->assertStatus(201)
    ->assertJsonStructure([
      'status',
      'message',
      'data' => [
        'user',
      ],
    ]);
});

// Đã loại bỏ test với JSON:API format vì không còn hỗ trợ
