<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermisoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('permisos')->insert([
            ['id' => 1, 'nombre' => 'Ver Roles', 'permiso_id' => null],
            ['id' => 2, 'nombre' => 'Ver Usuarios', 'permiso_id' => null],
            ['id' => 3, 'nombre' => 'Registrar Rol', 'permiso_id' => 1],
            ['id' => 4, 'nombre' => 'Modificar Rol', 'permiso_id' => 1],
            ['id' => 5, 'nombre' => 'Modificar Permisos', 'permiso_id' => 2],
            ['id' => 6, 'nombre' => 'Registrar Usuarios', 'permiso_id' => 2],
            ['id' => 7, 'nombre' => 'Modificar Usuarios', 'permiso_id' => 2],
            ['id' => 8, 'nombre' => 'Modificar Contrasena', 'permiso_id' => 2],
            ['id' => 9, 'nombre' => 'Activar Usuario', 'permiso_id' => 2],
            ['id' => 10, 'nombre' => 'Ver Campanias', 'permiso_id' => null],
            ['id' => 11, 'nombre' => 'Registrar Campanias', 'permiso_id' => 10],
            ['id' => 12, 'nombre' => 'Modificar Campanias', 'permiso_id' => 10],
            ['id' => 13, 'nombre' => 'Anular Campanias', 'permiso_id' => 10],
            ['id' => 14, 'nombre' => 'Ver Especies', 'permiso_id' => null],
            ['id' => 15, 'nombre' => 'Registrar Especies', 'permiso_id' => 14],
            ['id' => 16, 'nombre' => 'Modificar Especies', 'permiso_id' => 14],
            ['id' => 17, 'nombre' => 'Eliminar Especies', 'permiso_id' => 14],
            ['id' => 18, 'nombre' => 'Ver Razas', 'permiso_id' => null],
            ['id' => 19, 'nombre' => 'Registrar Razas', 'permiso_id' => 18],
            ['id' => 20, 'nombre' => 'Modificar Razas', 'permiso_id' => 18],
            ['id' => 21, 'nombre' => 'Eliminar Razas', 'permiso_id' => 18],
            ['id' => 22, 'nombre' => 'Ver Categorias', 'permiso_id' => null],
            ['id' => 23, 'nombre' => 'Registrar Categorias', 'permiso_id' => 22],
            ['id' => 24, 'nombre' => 'Modificar Categorias', 'permiso_id' => 22],
            ['id' => 25, 'nombre' => 'Eliminar Categorias', 'permiso_id' => 22],
            ['id' => 26, 'nombre' => 'Ver Tipos de Campania', 'permiso_id' => null],
            ['id' => 27, 'nombre' => 'Registrar Tipos de Campania', 'permiso_id' => 26],
            ['id' => 28, 'nombre' => 'Modificar Tipos de Campania', 'permiso_id' => 26],
            ['id' => 29, 'nombre' => 'Eliminar Tipos de Campania', 'permiso_id' => 26],
            ['id' => 30, 'nombre' => 'Registro de persona mascota', 'permiso_id' => null],
            ['id' => 31, 'nombre' => 'Busqueda', 'permiso_id' => null],
            ['id' => 32, 'nombre' => 'Denuncia', 'permiso_id' => null],
            ['id' => 33, 'nombre' => 'Reporte denuncia', 'permiso_id' => null],
            ['id' => 34, 'nombre' => 'Tipo de denuncia', 'permiso_id' => null],
            ['id' => 35, 'nombre' => 'Anular Registro de Vacuna', 'permiso_id' => null],
            ['id' => 36, 'nombre' => 'Ver Lugares', 'permiso_id' => null],
            ['id' => 37, 'nombre' => 'Registrar Lugares', 'permiso_id' => 36],
            ['id' => 38, 'nombre' => 'Modificar Lugares', 'permiso_id' => 36],
            ['id' => 39, 'nombre' => 'Eliminar Lugares', 'permiso_id' => 36],
            ['id' => 40, 'nombre' => 'Ver Registro Vacunas', 'permiso_id' => null],
            ['id' => 41, 'nombre' => 'Registrar Registro Vacunas', 'permiso_id' => 40],
        ]);
    }
}
