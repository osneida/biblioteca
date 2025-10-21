<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AutorFactory extends Factory
{

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->name,
            'nacionalidad' => 'V',
            'fecha_nacimiento' => $this->faker->date(),
            'fecha_fallecimiento' => null,
            'biografia' => $this->faker->text(500),
        ];
    }
}
