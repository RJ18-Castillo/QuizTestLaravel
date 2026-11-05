<?php

namespace Tests\Feature;

use App\Models\Grupo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\DatosDePrueba;
use Tests\TestCase;

/** CP-01: Registrar un grupo con toda su información y su curso. */
class CP01RegistrarGrupoTest extends TestCase
{
    use RefreshDatabase, DatosDePrueba;

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
}
