<?php

namespace Tests\Unit;

use App\Models\Docente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocenteTest extends TestCase
{
    use RefreshDatabase;

    public function test_docente_guarda_sus_atributos(): void
    {
        Docente::create([
            'cod_empleado' => 1001,
            'nombre_empleado' => 'Ana',
            'apellidos_empleado' => 'Mora Solís',
        ]);

        $this->assertDatabaseHas('docentes', [
            'cod_empleado' => 1001,
            'nombre_empleado' => 'Ana',
            'apellidos_empleado' => 'Mora Solís',
        ]);
    }
}
