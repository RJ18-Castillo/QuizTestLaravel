<?php

namespace Tests\Feature;

use App\Models\Grupo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\DatosDePrueba;
use Tests\TestCase;

/** CP-02: Un curso tiene varios grupos y se distinguen los abiertos. */
class CP02CursoConVariosGruposTest extends TestCase
{
    use RefreshDatabase, DatosDePrueba;

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
}
