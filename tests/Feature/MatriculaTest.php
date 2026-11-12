<?php

use App\Models\Curso;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\EstudianteMatriculado;
use App\Models\Grupo;
use Illuminate\Database\QueryException;

mutates(Grupo::class);

/*
 | IF6100 - Segunda entrega. Casos CP-03 a CP-13 contra el contrato actualizado.
 |
 | Cada test lleva su ID al inicio y la etiqueta nuevoCPxx para poder correrlo solo:
 |   php artisan test --filter=nuevoCP05     (o --filter=CP-05)
 |
 | Ningún test depende de otro: cada uno arma sus propios datos.
 */

// ---------- Datos de prueba que se repiten ----------

/** Grupo 01: curso IF6100, ciclo 2, año 2026, grupo 1, cerrado. */
function grupo01(): Grupo
{
    $curso = Curso::factory()->create([
        'sigla' => 'IF6100',
        'nombre_curso' => 'Análisis y Diseño de Sistemas',
        'creditos' => 4,
    ]);

    return Grupo::factory()->for($curso)->create([
        'numero_ciclo' => 2,
        'ano' => 2026,
        'numero_grupo' => 1,
        'abierto' => false,
    ]);
}

/** Docentes fijos del enunciado: 1001, 1002 y 1003. */
function docenteFijo(int $codEmpleado): Docente
{
    $datos = [
        1001 => ['Ana', 'Mora Vargas'],
        1002 => ['Luis', 'Rojas Solano'],
        1003 => ['Carla', 'Jiménez Araya'],
    ];
    [$nombre, $apellidos] = $datos[$codEmpleado];

    return Docente::factory()->create([
        'cod_empleado' => $codEmpleado,
        'nombre_empleado' => $nombre,
        'apellidos_empleado' => $apellidos,
    ]);
}

/** Estudiante C12345, "María", "Solís Quesada". */
function estudianteC12345(Docente $consejero): Estudiante
{
    return Estudiante::factory()->create([
        'carne' => 'C12345',
        'nombre' => 'María',
        'apellidos' => 'Solís Quesada',
        'consejero_id' => $consejero->id,
    ]);
}

/** "Otros estudiantes": se generan con factory (carné único) y se matriculan en el grupo. */
function matricularConFactory(Grupo $grupo, int $cantidad): void
{
    $estudiantes = Estudiante::factory()
        ->count($cantidad)
        ->for(Docente::factory(), 'consejero')
        ->create();

    $grupo->estudiantes()->attach($estudiantes->pluck('id')->all());
}

/** Fila de la matrícula de un estudiante en un grupo. */
function matricula(Grupo $grupo, Estudiante $estudiante): ?EstudianteMatriculado
{
    return EstudianteMatriculado::where('grupo_id', $grupo->id)
        ->where('estudiante_id', $estudiante->id)
        ->first();
}

// ---------- CP-03 a CP-06: reglas de Grupo (RN-02, RN-05) ----------

test('CP-03 nuevoCP03: un grupo acepta dos docentes', function () {
    $grupo = grupo01();
    $d1001 = docenteFijo(1001);
    $d1002 = docenteFijo(1002);

    $grupo->asignarDocente($d1001);
    $grupo->asignarDocente($d1002);

    $docentes = $grupo->fresh()->docentes;
    expect($docentes)->toHaveCount(2)
        ->and($docentes->pluck('cod_empleado')->all())->toEqualCanonicalizing([1001, 1002])
        ->and($d1001->fresh()->grupos->contains($grupo))->toBeTrue();

    $this->assertDatabaseCount('grupo_docente', 2);
});

test('CP-04 nuevoCP04: un grupo rechaza un tercer docente', function () {
    $grupo = grupo01();
    $grupo->asignarDocente(docenteFijo(1001));
    $grupo->asignarDocente(docenteFijo(1002));
    $d1003 = docenteFijo(1003);

    expect(fn () => $grupo->asignarDocente($d1003))->toThrow(DomainException::class);

    expect($grupo->fresh()->docentes)->toHaveCount(2);
    $this->assertDatabaseMissing('grupo_docente', [
        'grupo_id' => $grupo->id,
        'docente_id' => $d1003->id,
    ]);
});

test('CP-05 nuevoCP05: un grupo con 14 estudiantes no puede abrirse', function () {
    $grupo = grupo01();
    $grupo->asignarDocente(docenteFijo(1001));
    matricularConFactory($grupo, 14);

    expect($grupo->puedeAbrirse())->toBeFalse();
    expect(fn () => $grupo->abrir())->toThrow(DomainException::class);

    expect($grupo->fresh()->abierto)->toBeFalse();
});

test('CP-06 nuevoCP06: un grupo con 15 estudiantes y un docente se abre', function () {
    $grupo = grupo01();
    $grupo->asignarDocente(docenteFijo(1001));
    matricularConFactory($grupo, 15);

    expect($grupo->puedeAbrirse())->toBeTrue();

    $grupo->abrir();

    expect($grupo->fresh()->abierto)->toBeTrue();
});

// ---------- CP-07: grupo sin docente ----------

test('CP-07 nuevoCP07: un grupo sin docente no se abre aunque tenga cupo completo', function () {
    $grupo = grupo01();
    matricularConFactory($grupo, 20);

    expect($grupo->docentes()->count())->toBe(0)
        ->and($grupo->estudiantes()->count())->toBe(20);

    expect($grupo->puedeAbrirse())->toBeFalse();
    expect(fn () => $grupo->abrir())->toThrow(DomainException::class);

    expect($grupo->fresh()->abierto)->toBeFalse();
});

// ---------- CP-08 y CP-09: consejero (RN-03) ----------

test('CP-08 nuevoCP08: un estudiante queda ligado a su profesor consejero', function () {
    $d1001 = docenteFijo(1001);
    expect($d1001->estudiantes()->count())->toBe(0);

    $estudiante = estudianteC12345($d1001);

    expect($estudiante->consejero->cod_empleado)->toBe(1001);

    $aconsejados = $d1001->fresh()->estudiantes;
    expect($aconsejados)->toHaveCount(1)
        ->and($aconsejados->pluck('carne')->all())->toBe(['C12345']);

    expect($estudiante->fresh()->consejero_id)->toBe($d1001->id);
});

test('CP-09 nuevoCP09: cambiar de consejero reemplaza al anterior, no lo suma', function () {
    $d1001 = docenteFijo(1001);
    $d1002 = docenteFijo(1002);
    $estudiante = estudianteC12345($d1001);

    $estudiante->consejero()->associate($d1002);
    $estudiante->save();

    $d1001->refresh()->load('estudiantes');
    $d1002->refresh()->load('estudiantes');

    expect($estudiante->fresh()->consejero->cod_empleado)->toBe(1002)
        ->and($d1001->estudiantes)->toHaveCount(0)
        ->and($d1002->estudiantes)->toHaveCount(1);

    expect(Estudiante::where('carne', 'C12345')->pluck('consejero_id')->all())->toBe([$d1002->id]);
});

// ---------- CP-10 a CP-13: matrícula y nota (RN-04, RN-06, RN-07) ----------

test('CP-10 nuevoCP10: al matricular, la nota queda vacía', function () {
    $grupo = grupo01();
    $estudiante = estudianteC12345(docenteFijo(1001));

    $grupo->estudiantes()->attach($estudiante->id);

    $pivote = $grupo->load('estudiantes')->estudiantes->first()->pivot;
    expect($pivote)->toBeInstanceOf(EstudianteMatriculado::class)
        ->and($pivote->nota)->toBeNull();

    $this->assertDatabaseCount('estudiantes_matriculados', 1);
    $this->assertDatabaseHas('estudiantes_matriculados', [
        'grupo_id' => $grupo->id,
        'estudiante_id' => $estudiante->id,
    ]);
});

test('CP-11 nuevoCP11: se registra la nota al finalizar el curso', function () {
    $grupo = grupo01();
    $estudiante = estudianteC12345(docenteFijo(1001));
    $otro = Estudiante::factory()->create();
    $grupo->estudiantes()->attach([$estudiante->id, $otro->id]);

    expect(matricula($grupo, $estudiante)->nota)->toBeNull();

    $grupo->registrarNota($estudiante, 8.5);

    $nota = matricula($grupo, $estudiante)->nota;
    expect($nota)->toBeFloat()->toBe(8.5);

    // Solo cambió la nota de C12345; el resto de la matrícula queda igual.
    expect(matricula($grupo, $estudiante)->grupo_id)->toBe($grupo->id)
        ->and(matricula($grupo, $otro)->nota)->toBeNull();
    $this->assertDatabaseCount('estudiantes_matriculados', 2);
});

test('CP-12 nuevoCP12: se rechazan notas menores que 0 o mayores que 10', function (float $nota) {
    $grupo = grupo01();
    $estudiante = estudianteC12345(docenteFijo(1001));
    $grupo->estudiantes()->attach($estudiante->id);

    expect(fn () => $grupo->registrarNota($estudiante, $nota))
        ->toThrow(InvalidArgumentException::class);

    expect(matricula($grupo, $estudiante)->nota)->toBeNull();
})->with([-1, 10.5]);

test('CP-13 nuevoCP13: un estudiante no se matricula dos veces en el mismo grupo', function () {
    $grupo = grupo01();
    $estudiante = estudianteC12345(docenteFijo(1001));
    $grupo->estudiantes()->attach($estudiante->id);

    expect(fn () => $grupo->estudiantes()->attach($estudiante->id))
        ->toThrow(QueryException::class);

    expect(EstudianteMatriculado::where('grupo_id', $grupo->id)
        ->where('estudiante_id', $estudiante->id)
        ->count())->toBe(1);
});
