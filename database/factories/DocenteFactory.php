<?php

namespace Database\Factories;

use App\Models\Docente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Docente>
 */
class DocenteFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Desde 2000 para no chocar con los docentes fijos 1001, 1002 y 1003.
            'cod_empleado' => fake()->unique()->numberBetween(2000, 999999),
            'nombre_empleado' => fake()->firstName(),
            'apellidos_empleado' => fake()->lastName() . ' ' . fake()->lastName(),
        ];
    }
}
