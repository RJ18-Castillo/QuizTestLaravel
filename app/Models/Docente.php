<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Docente extends Model
{
    protected $fillable = ['cod_empleado', 'nombre_empleado', 'apellidos_empleado'];

    public function estudiantes(){
        return $this->hasMany(Estudiante::class);
    }
    public function grupos()
    {
        return $this->belongsToMany(Grupo::class, 'docente_grupo')->withTimestamps();
    }
}
