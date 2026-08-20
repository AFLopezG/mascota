<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('permisos')->where('id', 46)->exists();

        if (!$exists) {
            DB::table('permisos')->insert([
                'id' => 46,
                'nombre' => 'Reporte Registro Vacunas',
                'permiso_id' => 40,
            ]);
        }

        $assigned = DB::table('permiso_rol')
            ->where('rol_id', 1)
            ->where('permiso_id', 46)
            ->exists();

        if (!$assigned) {
            DB::table('permiso_rol')->insert([
                'rol_id' => 1,
                'permiso_id' => 46,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('permiso_rol')
            ->where('rol_id', 1)
            ->where('permiso_id', 46)
            ->delete();

        DB::table('permisos')
            ->where('id', 46)
            ->delete();
    }
};
