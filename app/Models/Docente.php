<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Docente extends Model
{
    /** @use HasFactory<\Database\Factories\DocenteFactory> */
    use HasFactory;

    protected $fillable = ['cod_empleado', 'nombre_empleado', 'apellidos_empleado'];

    /** Estudiantes que aconseja (RN-03). */
    public function estudiantes()
    {
        return $this->hasMany(Estudiante::class, 'consejero_id');
    }

    public function grupos()
    {
        return $this->belongsToMany(Grupo::class, 'grupo_docente')->withTimestamps();
    }
}
