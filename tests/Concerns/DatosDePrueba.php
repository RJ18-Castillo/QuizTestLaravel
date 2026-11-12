<?php

namespace Tests\Concerns;

use App\Models\Curso;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Grupo;

/**
 * IF6100 - "Datos de prueba que se repiten", creados a mano (sin factories).
 */
trait DatosDePrueba
{
    protected function crearCurso(): Curso
    {
        return Curso::create([
            'sigla' => 'IF6100',
            'nombre_curso' => 'Análisis y Diseño de Sistemas',
            'creditos' => 4,
        ]);
    }

    protected function crearDocente(int $cod, string $nombre, string $apellidos): Docente
    {
        return Docente::create([
            'cod_empleado' => $cod,
            'nombre_empleado' => $nombre,
            'apellidos_empleado' => $apellidos,
        ]);
    }

    protected function docente1001(): Docente
    {
        return $this->crearDocente(1001, 'Ana', 'Mora Vargas');
    }

    protected function docente1002(): Docente
    {
        return $this->crearDocente(1002, 'Luis', 'Rojas Solano');
    }

    protected function docente1003(): Docente
    {
        return $this->crearDocente(1003, 'Carla', 'Jiménez Araya');
    }

    /** Grupo 01: curso IF6100, ciclo 2, año 2026, grupo 1. */
    protected function crearGrupo01(Curso $curso, bool $abierto): Grupo
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
    protected function matricularEstudiantes(Grupo $grupo, Docente $consejero, int $cantidad): void
    {
        for ($i = 1; $i <= $cantidad; $i++) {
            $estudiante = Estudiante::create([
                'carne' => sprintf('C%05d', $i),
                'nombre' => "Estudiante$i",
                'apellidos' => "Apellido$i",
                'consejero_id' => $consejero->id,
            ]);
            $grupo->estudiantes()->attach($estudiante->id);
        }
    }
}
