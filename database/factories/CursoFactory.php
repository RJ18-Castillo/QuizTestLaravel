<?php

namespace Database\Factories;

use App\Models\Curso;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Curso>
 */
class CursoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sigla' => 'IF' . fake()->unique()->numerify('####'),
            'nombre_curso' => fake()->sentence(3),
            'creditos' => fake()->numberBetween(1, 4),
        ];
    }
}
