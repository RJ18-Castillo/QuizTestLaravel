<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class Grupo extends Model
{
    /** @use HasFactory<\Database\Factories\GrupoFactory> */
    use HasFactory;

    public const MAX_DOCENTES = 2;
    public const MIN_ESTUDIANTES_PARA_ABRIR = 15;
    public const NOTA_MINIMA = 0;
    public const NOTA_MAXIMA = 10;

    protected $fillable = ['numero_ciclo', 'ano', 'numero_grupo', 'abierto', 'curso_id'];

    protected $casts = ['abierto' => 'boolean'];

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function docentes()
    {
        return $this->belongsToMany(Docente::class, 'grupo_docente')->withTimestamps();
    }

    public function estudiantes()
    {
        return $this->belongsToMany(Estudiante::class, 'estudiantes_matriculados')
            ->using(EstudianteMatriculado::class)
            ->withPivot('nota')
            ->withTimestamps();
    }

    /**
     * RN-02: cada grupo lo imparten 1 o 2 docentes.
     *
     * @throws DomainException si el grupo ya tiene 2 docentes.
     */
    public function asignarDocente(Docente $docente): void
    {
        if ($this->docentes()->count() >= self::MAX_DOCENTES) {
            throw new DomainException(
                'Un grupo no puede tener más de ' . self::MAX_DOCENTES . ' docentes.'
            );
        }

        $this->docentes()->attach($docente->id);
    }

    /**
     * RN-05 y RN-02: se abre con al menos 15 estudiantes y al menos 1 docente.
     */
    public function puedeAbrirse(): bool
    {
        return $this->estudiantes()->count() >= self::MIN_ESTUDIANTES_PARA_ABRIR
            && $this->docentes()->count() >= 1;
    }

    /**
     * @throws DomainException si no se cumplen las condiciones para abrir.
     */
    public function abrir(): void
    {
        if (! $this->puedeAbrirse()) {
            throw new DomainException(
                'El grupo necesita al menos ' . self::MIN_ESTUDIANTES_PARA_ABRIR
                . ' estudiantes y 1 docente para abrirse.'
            );
        }

        $this->abierto = true;
        $this->save();
    }

    /**
     * RN-04 y RN-06: guarda la nota de la matrícula; debe estar entre 0 y 10.
     *
     * @throws InvalidArgumentException si la nota está fuera de 0-10.
     */
    public function registrarNota(Estudiante $estudiante, float $nota): void
    {
        if ($nota < self::NOTA_MINIMA || $nota > self::NOTA_MAXIMA) {
            throw new InvalidArgumentException(
                'La nota debe estar entre ' . self::NOTA_MINIMA . ' y ' . self::NOTA_MAXIMA . '.'
            );
        }

        $this->estudiantes()->updateExistingPivot($estudiante->id, ['nota' => $nota]);
    }
}
