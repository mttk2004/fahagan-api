<?php

test('new users can register in json api format', function () {
    $response = $this->post('/api/register', [
      'data' => [
        'attributes' => [
          'first_name' => 'Nguyễn',
          'last_name' => 'Test',
          'email' => 'test@example.com',
          'phone' => '0987654321',
          'password' => 'password',
          'password_confirmation' => 'password',
        ],
      ],
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

test('new users can register in direct format', function () {
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
