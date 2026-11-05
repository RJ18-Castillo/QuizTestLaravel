<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\DatosDePrueba;
use Tests\TestCase;

/** CP-06: Un grupo con 15 estudiantes y un docente se abre. */
class CP06Grupo15EstudiantesSeAbreTest extends TestCase
{
    use RefreshDatabase, DatosDePrueba;

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
