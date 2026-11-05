<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Grupo;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * IF6100 - Casos de prueba CP-01 a CP-06.
 * Los datos se crean a mano (sin factories), según "Datos de prueba que se repiten".
 */
class CasosPrueba01a06Test extends TestCase
{
    use RefreshDatabase;

    // ---------- Datos de prueba que se repiten ----------

    private function crearCurso(): Curso
    {
        return Curso::create([
            'sigla' => 'IF6100',
            'nombre_curso' => 'Análisis y Diseño de Sistemas',
            'creditos' => 4,
        ]);
    }

    private function crearDocente(int $cod, string $nombre, string $apellidos): Docente
    {
        return Docente::create([
            'cod_empleado' => $cod,
            'nombre_empleado' => $nombre,
            'apellidos_empleado' => $apellidos,
        ]);
    }

    private function docente1001(): Docente
    {
        return $this->crearDocente(1001, 'Ana', 'Mora Vargas');
    }

    private function docente1002(): Docente
    {
        return $this->crearDocente(1002, 'Luis', 'Rojas Solano');
    }

    private function docente1003(): Docente
    {
        return $this->crearDocente(1003, 'Carla', 'Jiménez Araya');
    }

    /** Grupo 01: curso IF6100, ciclo 2, año 2026, grupo 1. */
    private function crearGrupo01(Curso $curso, bool $abierto): Grupo
    {
        return Grupo::create([
            'numero_ciclo' => 2,
            'ano' => 2026,
            'numero_grupo' => 1,
            'abierto' => $abierto,
            'curso_id' => $curso->id,
        ]);
    }

    /** Crea y matricula $cantidad estudiantes con carné único (C00001, C00002, ...). */
    private function matricularEstudiantes(Grupo $grupo, Docente $consejero, int $cantidad): void
    {
        for ($i = 1; $i <= $cantidad; $i++) {
            $estudiante = Estudiante::create([
                'carne' => sprintf('C%05d', $i),
                'nombre' => "Estudiante$i",
                'apellidos' => "Apellido$i",
                'docente_id' => $consejero->id,
            ]);
            $grupo->estudiantes()->attach($estudiante->id);
        }
    }

    // ---------- CP-01 ----------

    public function test_cp01_registrar_grupo_con_su_informacion_y_su_curso(): void
    {
        $curso = $this->crearCurso();
        $this->crearGrupo01($curso, true);

        $grupo = Grupo::find(1);

        $this->assertSame(2, $grupo->numero_ciclo);
        $this->assertSame(2026, $grupo->ano);
        $this->assertSame(1, $grupo->numero_grupo);
        $this->assertIsBool($grupo->abierto);   // booleano, no 1
        $this->assertTrue($grupo->abierto);

        $this->assertSame('IF6100', $grupo->curso->sigla);
        $this->assertSame(4, $grupo->curso->creditos);

        $this->assertSame(1, Grupo::count());
    }

    // ---------- CP-02 ----------

    public function test_cp02_un_curso_tiene_varios_grupos_y_se_distinguen_los_abiertos(): void
    {
        $curso = $this->crearCurso();

        foreach ([1 => true, 2 => true, 3 => false] as $numero => $abierto) {
            Grupo::create([
                'numero_ciclo' => 2,
                'ano' => 2026,
                'numero_grupo' => $numero,
                'abierto' => $abierto,
                'curso_id' => $curso->id,
            ]);
        }

        $grupos = $curso->fresh()->grupos;
        $abiertos = $grupos->filter(fn (Grupo $g) => $g->abierto === true);

        $this->assertCount(3, $grupos);
        $this->assertCount(2, $abiertos);
        $this->assertEqualsCanonicalizing([1, 2], $abiertos->pluck('numero_grupo')->all());
        $this->assertSame([$curso->id], $grupos->pluck('curso_id')->unique()->values()->all());
    }

    // ---------- CP-03 ----------

    public function test_cp03_un_grupo_acepta_dos_docentes(): void
    {
        $curso = $this->crearCurso();
        $grupo = $this->crearGrupo01($curso, false);
        $d1001 = $this->docente1001();
        $this->docente1002();

        $grupo->asignarDocente(1001);
        $grupo->asignarDocente(1002);

        $docentes = $grupo->fresh()->docentes;
        $this->assertCount(2, $docentes);
        $this->assertEqualsCanonicalizing([1001, 1002], $docentes->pluck('cod_empleado')->all());

        $this->assertTrue($d1001->fresh()->grupos->contains($grupo));
        $this->assertSame(2, \DB::table('docente_grupo')->count());
    }

    // ---------- CP-04 ----------

    public function test_cp04_un_grupo_rechaza_un_tercer_docente(): void
    {
        $curso = $this->crearCurso();
        $grupo = $this->crearGrupo01($curso, false);
        $this->docente1001();
        $this->docente1002();
        $d1003 = $this->docente1003();
        $grupo->asignarDocente(1001);
        $grupo->asignarDocente(1002);

        try {
            $grupo->asignarDocente(1003);
            $this->fail('Se esperaba DomainException al asignar un tercer docente.');
        } catch (DomainException $e) {
            $this->assertNotEmpty($e->getMessage());
        }

        $this->assertCount(2, $grupo->fresh()->docentes);
        $this->assertDatabaseMissing('docente_grupo', [
            'grupo_id' => $grupo->id,
            'docente_id' => $d1003->id,
        ]);
    }

    // ---------- CP-05 ----------

    public function test_cp05_un_grupo_con_14_estudiantes_no_puede_abrirse(): void
    {
        $curso = $this->crearCurso();
        $grupo = $this->crearGrupo01($curso, false);
        $d1001 = $this->docente1001();
        $grupo->asignarDocente(1001);
        $this->matricularEstudiantes($grupo, $d1001, 14);

        $this->assertFalse($grupo->puedeAbrirse());

        try {
            $grupo->abrir();
            $this->fail('Se esperaba DomainException al abrir con 14 estudiantes.');
        } catch (DomainException $e) {
            $this->assertNotEmpty($e->getMessage());
        }

        $this->assertFalse($grupo->fresh()->abierto);
    }

    // ---------- CP-06 ----------

    public function test_cp06_un_grupo_con_15_estudiantes_y_un_docente_se_abre(): void
    {
        $curso = $this->crearCurso();
        $grupo = $this->crearGrupo01($curso, false);
        $d1001 = $this->docente1001();
        $grupo->asignarDocente(1001);
        $this->matricularEstudiantes($grupo, $d1001, 15);

        $this->assertTrue($grupo->puedeAbrirse());

        $grupo->abrir(); // no debe lanzar excepción

        $this->assertTrue($grupo->fresh()->abierto);
    }
}
