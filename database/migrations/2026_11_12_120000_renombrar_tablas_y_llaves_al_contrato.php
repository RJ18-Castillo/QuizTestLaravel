<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Segunda entrega IF6100: el contrato fija estos nombres.
 *  - docente_grupo            -> grupo_docente
 *  - estudiante_matriculado   -> estudiantes_matriculados
 *  - estudiantes.docente_id   -> estudiantes.consejero_id
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('docente_grupo', 'grupo_docente');
        Schema::rename('estudiante_matriculado', 'estudiantes_matriculados');

        Schema::table('estudiantes', function (Blueprint $table) {
            $table->renameColumn('docente_id', 'consejero_id');
        });
    }

    public function down(): void
    {
        Schema::table('estudiantes', function (Blueprint $table) {
            $table->renameColumn('consejero_id', 'docente_id');
        });

        Schema::rename('estudiantes_matriculados', 'estudiante_matriculado');
        Schema::rename('grupo_docente', 'docente_grupo');
    }
};
