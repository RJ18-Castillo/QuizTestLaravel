<?php

namespace Tests\Unit;

use App\Models\Curso;
use App\Models\Docente;
use App\Models\Grupo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GrupoModeloTest extends TestCase
{
    use RefreshDatabase;

    private function curso(): Curso
    {
        return Curso::create([
            'sigla' => 'IF6100',
            'nombre_curso' => 'Análisis y Diseño de Sistemas',
            'creditos' => 4,
        ]);
    }

    private function grupo(): Grupo
    {
        return Grupo::create([
            'numero_ciclo' => 2,
            'ano' => 2026,
            'numero_grupo' => 1,
            'abierto' => true,
            'curso_id' => $this->curso()->id,
        ]);
    }

    public function test_grupo_guarda_sus_atributos(): void
    {
        $grupo = $this->grupo()->fresh();

        $this->assertSame(2, $grupo->numero_ciclo);
        $this->assertSame(2026, $grupo->ano);
        $this->assertSame(1, $grupo->numero_grupo);
        $this->assertTrue($grupo->abierto);
    }

    public function test_grupo_pertenece_a_un_curso_y_curso_tiene_varios_grupos(): void
    {
        $curso = $this->curso();
        foreach ([1, 2] as $n) {
            Grupo::create([
                'numero_ciclo' => 2,
                'ano' => 2026,
                'numero_grupo' => $n,
                'abierto' => true,
                'curso_id' => $curso->id,
            ]);
        }

        $this->assertCount(2, $curso->fresh()->grupos);
        $this->assertTrue($curso->grupos->first()->curso->is($curso));
    }

    public function test_grupo_es_impartido_por_uno_o_dos_docentes(): void
    {
        $grupo = $this->grupo();
        $d1 = Docente::create(['cod_empleado' => 1, 'nombre_empleado' => 'Ana', 'apellidos_empleado' => 'Mora']);
        $d2 = Docente::create(['cod_empleado' => 2, 'nombre_empleado' => 'Luis', 'apellidos_empleado' => 'Rojas']);

        $grupo->docentes()->attach([$d1->id, $d2->id]);

        $this->assertCount(2, $grupo->fresh()->docentes);
        $this->assertTrue($d1->fresh()->grupos->contains($grupo));
    }
}
