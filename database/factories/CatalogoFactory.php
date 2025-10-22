<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CatalogoFactory extends Factory
{
    public function definition(): array
    {
        $editorial = \App\Models\Editorial::factory()->create();

        return [
            "ano_publicacion" => $this->faker->year(),
            "tipo_documento" => 1,
            "isbn" => null,
            "titulo" => $this->faker->name(),
            "subtitulo" =>  $this->faker->text(),
            "editorial_id" => $editorial->id,
            "descripcion_fisica" => "Esta bonito",
            "notas" => "tiene varios ejemplares",
            "user_id" => 1
        ];
    }
}
