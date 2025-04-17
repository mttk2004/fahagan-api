<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('authenticated users can change password with valid data', function () {
  $user = User::factory()->create([
    'password' => bcrypt('oldpassword123')
  ]);

  Sanctum::actingAs($user);

  $response = $this->post('/api/auth/change-password', [
    'old_password' => 'oldpassword123',
    'new_password' => 'newpassword123',
    'new_password_confirmation' => 'newpassword123'
  ]);

  $response->assertStatus(204);

  // Verify the password was actually changed
  $updatedUser = User::find($user->id);
  expect(Hash::check('newpassword123', $updatedUser->password))->toBeTrue();
});

test('authenticated users cannot change password with incorrect old password', function () {
  $user = User::factory()->create([
    'password' => bcrypt('correctpassword')
  ]);

  Sanctum::actingAs($user);

  $response = $this->post('/api/auth/change-password', [
    'old_password' => 'wrongpassword',
    'new_password' => 'newpassword123',
    'new_password_confirmation' => 'newpassword123'
  ]);

  $response->assertStatus(422);
});

test('authenticated users cannot change password with mismatched confirmation', function () {
  $user = User::factory()->create([
    'password' => bcrypt('oldpassword123')
  ]);

  Sanctum::actingAs($user);

  $response = $this->post('/api/auth/change-password', [
    'old_password' => 'oldpassword123',
    'new_password' => 'newpassword123',
    'new_password_confirmation' => 'different123'
  ]);

  $response->assertStatus(422)
    ->assertJsonValidationErrors(['new_password']);
});

test('unauthenticated users cannot change password', function () {
  $response = $this->post('/api/auth/change-password', [
    'old_password' => 'oldpassword123',
    'new_password' => 'newpassword123',
    'new_password_confirmation' => 'newpassword123'
  ]);

  $response->assertStatus(401);
});
