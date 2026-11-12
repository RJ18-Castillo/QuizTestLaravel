<?php

namespace Database\Factories;

use App\Models\Docente;
use App\Models\Estudiante;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Estudiante>
 */
class EstudianteFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Prefijo F para no chocar con el carné fijo C12345.
            'carne' => 'F' . fake()->unique()->numerify('#####'),
            'nombre' => fake()->firstName(),
            'apellidos' => fake()->lastName() . ' ' . fake()->lastName(),
            'consejero_id' => Docente::factory(),
        ];
    }
}
