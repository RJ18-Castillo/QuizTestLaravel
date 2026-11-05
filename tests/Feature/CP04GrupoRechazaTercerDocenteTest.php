<?php

namespace Tests\Feature;

use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\DatosDePrueba;
use Tests\TestCase;

/** CP-04: Un grupo rechaza un tercer docente. */
class CP04GrupoRechazaTercerDocenteTest extends TestCase
{
    use RefreshDatabase, DatosDePrueba;

    public function test_cp04_un_grupo_rechaza_un_tercer_docente(): void
    {
        $curso = $this->crearCurso();
        $grupo = $this->crearGrupo01($curso, false);
        $this->docente1001();
        $this->docente1002();
        $d1003 = $this->docente1003();
        $grupo->asignarDocente(1001);
        $grupo->asignarDocente(1002);

        $this->expectException(DomainException::class);

        try {
            $grupo->asignarDocente(1003);
        } finally {
            $this->assertCount(2, $grupo->fresh()->docentes);
            $this->assertDatabaseMissing('docente_grupo', [
                'grupo_id' => $grupo->id,
                'docente_id' => $d1003->id,
            ]);
        }
    }
}
