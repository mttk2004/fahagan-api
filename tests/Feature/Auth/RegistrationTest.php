<?php

use App\Models\User;

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

test('users cannot register with existing email', function () {
  $user = User::factory()->create([
    'email' => 'exist@example.com'
  ]);
  $response = $this->post('/api/register', [
    'first_name' => 'Nguyễn',
    'last_name' => 'Test',
    'email' => $user->email,
    'phone' => '0987654321',
    'password' => 'password',
    'password_confirmation' => 'password',
  ]);
  $response->assertStatus(422)
    ->assertJsonValidationErrors(['email']);
});

test('users cannot register with existing phone', function () {
  $user = User::factory()->create([
    'phone' => '0987654321'
  ]);
  $response = $this->post('/api/register', [
    'first_name' => 'Nguyễn',
    'last_name' => 'Test',
    'email' => 'email@example.com',
    'phone' => $user->phone,
    'password' => 'password',
    'password_confirmation' => 'password',
  ]);
  $response->assertStatus(422)
    ->assertJsonValidationErrors(['phone']);
});

test('users cannot register with invalid email format', function () {
  $response = $this->post('/api/register', [
    'first_name' => 'Nguyễn',
    'last_name' => 'Test',
    'email' => 'invalid-email',
    'phone' => '0987654321',
    'password' => 'password',
    'password_confirmation' => 'password',
  ]);
  $response->assertStatus(422)
    ->assertJsonValidationErrors(['email']);
});

test('users cannot register with empty email', function () {
  $response = $this->post('/api/register', [
    'first_name' => 'Nguyễn',
    'last_name' => 'Test',
    'email' => '',
    'phone' => '0987654321',
    'password' => 'password',
    'password_confirmation' => 'password',
  ]);
  $response->assertStatus(422)
    ->assertJsonValidationErrors(['email']);
});

test('users cannot register with empty password', function () {
  $response = $this->post('/api/register', [
    'first_name' => 'Nguyễn',
    'last_name' => 'Test',
    'email' => 'email@example.com',
    'phone' => '0987654321',
    'password' => '',
    'password_confirmation' => 'password',
  ]);
  $response->assertStatus(422)
    ->assertJsonValidationErrors(['password']);
});

test('users cannot register with empty first name', function () {
  $response = $this->post('/api/register', [
    'first_name' => '',
    'last_name' => 'Test',
    'email' => 'email@example.com',
    'phone' => '0987654321',
    'password' => 'password',
    'password_confirmation' => 'password',
  ]);
  $response->assertStatus(422)
    ->assertJsonValidationErrors(['first_name']);
});

test('users cannot register with empty last name', function () {
  $response = $this->post('/api/register', [
    'first_name' => 'Nguyễn',
    'last_name' => '',
    'email' => 'email@example.com',
    'phone' => '0987654321',
    'password' => 'password',
    'password_confirmation' => 'password',
  ]);
  $response->assertStatus(422)
    ->assertJsonValidationErrors(['last_name']);
});

test('users cannot register with empty phone', function () {
  $response = $this->post('/api/register', [
    'first_name' => 'Nguyễn',
    'last_name' => 'Test',
    'email' => 'email@example.com',
    'phone' => '',
    'password' => 'password',
    'password_confirmation' => 'password',
  ]);
  $response->assertStatus(422)
    ->assertJsonValidationErrors(['phone']);
});

test('users cannot register with invalid phone format', function () {
  $response = $this->post('/api/register', [
    'first_name' => 'Nguyễn',
    'last_name' => 'Test',
    'email' => 'email@example.com',
    'phone' => 'invalid-phone',
    'password' => 'password',
    'password_confirmation' => 'password',
  ]);
  $response->assertStatus(422)
    ->assertJsonValidationErrors(['phone']);
});

test('users cannot register with mismatched password confirmation', function () {
  $response = $this->post('/api/register', [
    'first_name' => 'Nguyễn',
    'last_name' => 'Test',
    'email' => 'email@example.com',
    'phone' => '0987654321',
    'password' => 'password',
    'password_confirmation' => 'different-password',
  ]);
  $response->assertStatus(422)
    ->assertJsonValidationErrors(['password']);
});
