<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fakeCreatedAt = fake()->dateTimeBetween('-5 year');

        return [
            'first_name' => fake('vi_VN')->firstName(),
            'last_name' => fake('vi_VN')->lastName(),
            'phone' => fake()->regexify('0[35789][0-9]{8}'),
            'email' => fake('vi_VN')->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'is_customer' => true,
            'created_at' => $fakeCreatedAt,
            'updated_at' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
