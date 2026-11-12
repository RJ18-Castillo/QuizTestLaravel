<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    /** @use HasFactory<\Database\Factories\EstudianteFactory> */
    use HasFactory;

    protected $fillable = ['carne', 'nombre', 'apellidos', 'consejero_id'];

    /** Profesor consejero (RN-03): un estudiante tiene uno solo. */
    public function consejero()
    {
        return $this->belongsTo(Docente::class, 'consejero_id');
    }

    public function grupos()
    {
        return $this->belongsToMany(Grupo::class, 'estudiantes_matriculados')
            ->using(EstudianteMatriculado::class)
            ->withPivot('nota')
            ->withTimestamps();
    }
}
