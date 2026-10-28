<?php

namespace Tests\Unit;

use App\Models\Curso;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\EstudianteMatriculado;
use App\Models\Grupo;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstudianteMatriculadoTest extends TestCase
{
    use RefreshDatabase;

    private function escenario(): array
    {
        $curso = Curso::create([
            'sigla' => 'IF6100',
            'nombre_curso' => 'Análisis y Diseño de Sistemas',
            'creditos' => 4,
        ]);
        $grupo = Grupo::create([
            'numero_ciclo' => 2,
            'ano' => 2026,
            'numero_grupo' => 1,
            'abierto' => true,
            'curso_id' => $curso->id,
        ]);
        $docente = Docente::create([
            'cod_empleado' => 1,
            'nombre_empleado' => 'Ana',
            'apellidos_empleado' => 'Mora',
        ]);
        $estudiante = Estudiante::create([
            'carne' => 'C00001',
            'nombre' => 'Pedro',
            'apellidos' => 'Salas',
            'docente_id' => $docente->id,
        ]);

        return [$grupo, $estudiante];
    }

    public function test_estudiante_matriculado_registra_la_nota(): void
    {
        [$grupo, $estudiante] = $this->escenario();

        $grupo->estudiantes()->attach($estudiante->id, ['nota' => 85.5]);

        $this->assertDatabaseHas('estudiante_matriculado', [
            'grupo_id' => $grupo->id,
            'estudiante_id' => $estudiante->id,
            'nota' => 85.5,
        ]);
        $this->assertSame(85.5, $grupo->fresh()->estudiantes->first()->pivot->nota);
    }

    public function test_estudiante_conoce_sus_grupos_matriculados(): void
    {
        [$grupo, $estudiante] = $this->escenario();
        $grupo->estudiantes()->attach($estudiante->id, ['nota' => 90]);

        $this->assertTrue($estudiante->fresh()->grupos->contains($grupo));
    }

    public function test_la_nota_puede_registrarse_despues_de_matricular(): void
    {
        [$grupo, $estudiante] = $this->escenario();
        $grupo->estudiantes()->attach($estudiante->id);

        $grupo->estudiantes()->updateExistingPivot($estudiante->id, ['nota' => 78]);

        $this->assertSame(78.0, $grupo->fresh()->estudiantes->first()->pivot->nota);
    }

    public function test_no_se_puede_matricular_dos_veces_en_el_mismo_grupo(): void
    {
        [$grupo, $estudiante] = $this->escenario();
        $grupo->estudiantes()->attach($estudiante->id);

        $this->expectException(QueryException::class);
        $grupo->estudiantes()->attach($estudiante->id);
    }

    public function test_matricula_tiene_modelo_propio(): void
    {
        [$grupo, $estudiante] = $this->escenario();
        $grupo->estudiantes()->attach($estudiante->id, ['nota' => 88]);

        $m = EstudianteMatriculado::first();

        $this->assertTrue($m->grupo->is($grupo));
        $this->assertTrue($m->estudiante->is($estudiante));
        $this->assertSame(88.0, $m->nota);
    }
}
