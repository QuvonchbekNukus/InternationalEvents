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
        return [
            'first_name' => fake()->firstName(),
            'middle_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'phone' => fake()->unique()->numerify('998#########'),
            'password' => static::$password ??= Hash::make('password'),
            'position_ru' => null,
            'position_uz' => null,
            'position_cryl' => null,
            'department_id' => null,
            'rank_id' => 1,
            'avatar' => null,
            'last_login_at' => null,
            'is_active' => true,
        ];
    }

    /**
     * Backward-compatible state helper.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => []);
    }
}
