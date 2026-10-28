<?php

namespace Tests\Unit;

use App\Models\Curso;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CursoTest extends TestCase
{
    use RefreshDatabase;

    public function test_curso_guarda_sus_atributos(): void
    {
        Curso::create([
            'sigla' => 'IF6100',
            'nombre_curso' => 'Análisis y Diseño de Sistemas',
            'creditos' => 4,
        ]);

        $this->assertDatabaseHas('cursos', ['sigla' => 'IF6100', 'creditos' => 4]);
    }
}
