<?php

namespace Tests\Feature;

use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\DatosDePrueba;
use Tests\TestCase;

/** CP-05: Un grupo con 14 estudiantes no puede abrirse. */
class CP05Grupo14EstudiantesNoSeAbreTest extends TestCase
{
    use RefreshDatabase, DatosDePrueba;

    public function test_cp05_un_grupo_con_14_estudiantes_no_puede_abrirse(): void
    {
        $curso = $this->crearCurso();
        $grupo = $this->crearGrupo01($curso, false);
        $d1001 = $this->docente1001();
        $grupo->asignarDocente($d1001);
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
}
