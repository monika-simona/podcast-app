<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static $password;

    public function definition(): array
    {

        $roles = ['admin', 'author', 'user'];

        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => $this->faker->randomElement($roles),
            'remember_token' => Str::random(10),
        ];
    }

    public function admin()
    {
        return $this->state(fn() => ['role' => 'admin']);
    }

    public function author()
    {
        return $this->state(fn() => ['role' => 'author']);
    }

    public function user()
    {
        return $this->state(fn() => ['role' => 'user']);
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
