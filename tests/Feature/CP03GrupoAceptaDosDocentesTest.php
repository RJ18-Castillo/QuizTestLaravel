<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\DatosDePrueba;
use Tests\TestCase;

/** CP-03: Un grupo acepta dos docentes. */
class CP03GrupoAceptaDosDocentesTest extends TestCase
{
    use RefreshDatabase, DatosDePrueba;

    public function test_cp03_un_grupo_acepta_dos_docentes(): void
    {
        $curso = $this->crearCurso();
        $grupo = $this->crearGrupo01($curso, false);
        $d1001 = $this->docente1001();
        $d1002 = $this->docente1002();

        $grupo->asignarDocente($d1001);
        $grupo->asignarDocente($d1002);

        $docentes = $grupo->fresh()->docentes;
        $this->assertCount(2, $docentes);
        $this->assertEqualsCanonicalizing([1001, 1002], $docentes->pluck('cod_empleado')->all());

        $this->assertTrue($d1001->fresh()->grupos->contains($grupo));
        $this->assertSame(2, DB::table('grupo_docente')->count());
    }
}
