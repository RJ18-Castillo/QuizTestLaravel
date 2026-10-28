<?php

namespace Tests\Unit;

use App\Models\Docente;
use App\Models\Estudiante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstudianteTest extends TestCase
{
    use RefreshDatabase;

    private function docente(): Docente
    {
        return Docente::create([
            'cod_empleado' => 1001,
            'nombre_empleado' => 'Ana',
            'apellidos_empleado' => 'Mora Solís',
        ]);
    }

    public function test_estudiante_guarda_sus_atributos(): void
    {
        Estudiante::create([
            'carne' => 'C00001',
            'nombre' => 'Pedro',
            'apellidos' => 'Salas Ruiz',
            'docente_id' => $this->docente()->id,
        ]);

        $this->assertDatabaseHas('estudiantes', [
            'carne' => 'C00001',
            'nombre' => 'Pedro',
            'apellidos' => 'Salas Ruiz',
        ]);
    }

    public function test_estudiante_tiene_un_solo_consejero(): void
    {
        $docente = $this->docente();
        $estudiante = Estudiante::create([
            'carne' => 'C00001',
            'nombre' => 'Pedro',
            'apellidos' => 'Salas Ruiz',
            'docente_id' => $docente->id,
        ]);

        $this->assertTrue($estudiante->consejero->is($docente));
    }

    public function test_docente_es_consejero_de_varios_estudiantes(): void
    {
        $docente = $this->docente();
        foreach (['C00001', 'C00002'] as $carne) {
            Estudiante::create([
                'carne' => $carne,
                'nombre' => 'X',
                'apellidos' => 'Y',
                'docente_id' => $docente->id,
            ]);
        }

        $this->assertCount(2, $docente->fresh()->estudiantes);
    }
}
