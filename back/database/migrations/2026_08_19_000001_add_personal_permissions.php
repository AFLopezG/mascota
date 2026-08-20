<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            ['id' => 47, 'nombre' => 'Ver Personals', 'permiso_id' => null],
            ['id' => 48, 'nombre' => 'Registrar Personals', 'permiso_id' => 47],
            ['id' => 49, 'nombre' => 'Modificar Personals', 'permiso_id' => 47],
            ['id' => 50, 'nombre' => 'Eliminar Personals', 'permiso_id' => 47],
        ];

        foreach ($permissions as $permission) {
            $exists = DB::table('permisos')->where('id', $permission['id'])->exists();

            if (!$exists) {
                DB::table('permisos')->insert($permission);
            }
        }

        foreach ([47, 48, 49, 50] as $permissionId) {
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
            ->whereIn('permiso_id', [47, 48, 49, 50])
            ->delete();

        DB::table('permisos')
            ->whereIn('id', [47, 48, 49, 50])
            ->delete();
    }
};
