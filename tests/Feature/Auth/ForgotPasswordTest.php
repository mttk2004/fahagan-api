<?php

test('validate email is required for forgot password', function () {
    $response = $this->post('/api/forgot-password', [
        'email' => '',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('users cannot request password reset with invalid email format', function () {
    $response = $this->post('/api/forgot-password', [
        'email' => 'invalid-email',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('users cannot request password reset with non-existent email', function () {
    $response = $this->post('/api/forgot-password', [
        'email' => 'nonexistent@example.com',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});
