<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'rol' => 'estudiante',
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'activo' => true,
        ];
    }
}
