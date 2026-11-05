<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    protected $fillable = ['numero_ciclo', 'ano', 'numero_grupo', 'abierto', 'curso_id'];

    protected $casts = ['abierto' => 'boolean'];

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function docentes()
    {
        return $this->belongsToMany(Docente::class, 'docente_grupo')->withTimestamps();
    }

    public function estudiantes()
    {
        return $this->belongsToMany(Estudiante::class, 'estudiante_matriculado')
            ->using(EstudianteMatriculado::class)
            ->withPivot('nota')
            ->withTimestamps();
    }

    public const MAX_DOCENTES = 2;
    public const MIN_ESTUDIANTES_PARA_ABRIR = 15;

    /**
     * RN-02: un grupo acepta como máximo 2 docentes.
     *
     * @throws \DomainException si el grupo ya tiene 2 docentes.
     */
    public function asignarDocente(int $codEmpleado): void
    {
        $docente = Docente::where('cod_empleado', $codEmpleado)->firstOrFail();

        if ($this->docentes()->where('docentes.id', $docente->id)->exists()) {
            return;
        }

        if ($this->docentes()->count() >= self::MAX_DOCENTES) {
            throw new \DomainException(
                'Un grupo no puede tener más de ' . self::MAX_DOCENTES . ' docentes.'
            );
        }

        $this->docentes()->attach($docente->id);
    }

    /**
     * RN-05 y RN-02: se abre con al menos 15 estudiantes y 1 docente.
     */
    public function puedeAbrirse(): bool
    {
        return $this->estudiantes()->count() >= self::MIN_ESTUDIANTES_PARA_ABRIR
            && $this->docentes()->count() >= 1;
    }

    /**
     * @throws \DomainException si no se cumplen las condiciones para abrir.
     */
    public function abrir(): void
    {
        if (! $this->puedeAbrirse()) {
            throw new \DomainException(
                'El grupo necesita al menos ' . self::MIN_ESTUDIANTES_PARA_ABRIR
                . ' estudiantes y 1 docente para abrirse.'
            );
        }

        $this->abierto = true;
        $this->save();
    }
}
