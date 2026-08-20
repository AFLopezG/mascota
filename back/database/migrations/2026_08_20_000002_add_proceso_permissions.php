<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            ['id' => 51, 'nombre' => 'Ver Procesos', 'permiso_id' => null],
            ['id' => 52, 'nombre' => 'Registrar Procesos', 'permiso_id' => 51],
            ['id' => 53, 'nombre' => 'Modificar Procesos', 'permiso_id' => 51],
            ['id' => 54, 'nombre' => 'Eliminar Procesos', 'permiso_id' => 51],
        ];

        foreach ($permissions as $permission) {
            $exists = DB::table('permisos')->where('id', $permission['id'])->exists();

            if (!$exists) {
                DB::table('permisos')->insert($permission);
            }
        }

        foreach ([51, 52, 53, 54] as $permissionId) {
            $assigned = DB::table('permiso_rol')
                ->where('rol_id', 1)
                ->where('permiso_id', $permissionId)
                ->exists();

            if (!$assigned) {
                DB::table('permiso_rol')->insert([
                    'rol_id' => 1,
                    'permiso_id' => $permissionId,
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('permiso_rol')
            ->where('rol_id', 1)
            ->whereIn('permiso_id', [51, 52, 53, 54])
            ->delete();

        DB::table('permisos')
            ->whereIn('id', [51, 52, 53, 54])
            ->delete();
    }
};
