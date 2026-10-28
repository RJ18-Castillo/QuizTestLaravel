<?php

namespace Tests\Unit;

use App\Models\Curso;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Grupo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GrupoTest extends TestCase
{
    use RefreshDatabase;

    public function test_grupo_contiene_toda_su_informacion(): void
    {
        $curso = Curso::create([
            'sigla' => 'IF6100',
            'nombre_curso' => 'Análisis y Diseño de Sistemas',
            'creditos' => 4,
        ]);

        $d1 = Docente::create([
            'cod_empleado' => 1001,
            'nombre_empleado' => 'Ana',
            'apellidos_empleado' => 'Mora Solís',
        ]);
        $d2 = Docente::create([
            'cod_empleado' => 1002,
            'nombre_empleado' => 'Luis',
            'apellidos_empleado' => 'Rojas Vega',
        ]);

        $grupo = Grupo::create([
            'numero_ciclo' => 2,
            'ano' => 2026,
            'numero_grupo' => 1,
            'abierto' => true,
            'curso_id' => $curso->id,
        ]);
        $grupo->docentes()->attach([$d1->id, $d2->id]);

        for ($i = 1; $i <= 15; $i++) {
            $est = Estudiante::create([
                'carne' => sprintf('C%05d', $i),
                'nombre' => "Estudiante$i",
                'apellidos' => "Apellido$i",
                'docente_id' => $d1->id,
            ]);
            $grupo->estudiantes()->attach($est->id, ['nota' => 70 + $i]);
        }

        $grupo = $grupo->fresh(['curso', 'docentes', 'estudiantes']);

        // Atributos propios del grupo
        $this->assertSame(2, $grupo->numero_ciclo);
        $this->assertSame(2026, $grupo->ano);
        $this->assertSame(1, $grupo->numero_grupo);
        $this->assertTrue($grupo->abierto);

        // Curso (1)
        $this->assertSame('IF6100', $grupo->curso->sigla);

        // Docentes (1..2)
        $this->assertGreaterThanOrEqual(1, $grupo->docentes->count());
        $this->assertLessThanOrEqual(2, $grupo->docentes->count());

        // Estudiantes matriculados (15..*), con nota y consejero
        $this->assertGreaterThanOrEqual(15, $grupo->estudiantes->count());
        $grupo->estudiantes->each(function ($e) {
            $this->assertNotNull($e->pivot->nota);
            $this->assertNotNull($e->consejero);
        });
    }
}
