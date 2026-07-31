<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('rols')->insert([
            ['id' => 1, 'nombre' => 'Administrador'],
            ['id' => 2, 'nombre' => 'Usuario'],
        ]);

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
        ]);

        DB::table('users')->insert([
            'cedula' => '1234567890',
            'name' => 'Admin',
            'nombre' => 'Administrador',
            'celular' => '1234567890',
            'fecha_limite' => '2999-12-31',
            'estado' => 'ACTIVO',
            'email' => 'Admin@test.com',
            'password' => Hash::make('admin123Admin'),
            'rol_id' => 1,
        ]);

        DB::table('permiso_rol')->insert([
            ['rol_id' => 1, 'permiso_id' => 1],
            ['rol_id' => 1, 'permiso_id' => 2],
            ['rol_id' => 1, 'permiso_id' => 3],
            ['rol_id' => 1, 'permiso_id' => 4],
            ['rol_id' => 1, 'permiso_id' => 5],
            ['rol_id' => 1, 'permiso_id' => 6],
            ['rol_id' => 1, 'permiso_id' => 7],
            ['rol_id' => 1, 'permiso_id' => 8],
            ['rol_id' => 1, 'permiso_id' => 9],
            ['rol_id' => 1, 'permiso_id' => 10],
            ['rol_id' => 1, 'permiso_id' => 11],
            ['rol_id' => 1, 'permiso_id' => 12],
            ['rol_id' => 1, 'permiso_id' => 13],
            ['rol_id' => 1, 'permiso_id' => 14],
            ['rol_id' => 1, 'permiso_id' => 15],
            ['rol_id' => 1, 'permiso_id' => 16],
            ['rol_id' => 1, 'permiso_id' => 17],
            ['rol_id' => 1, 'permiso_id' => 18],
            ['rol_id' => 1, 'permiso_id' => 19],
            ['rol_id' => 1, 'permiso_id' => 20],
            ['rol_id' => 1, 'permiso_id' => 21],
            ['rol_id' => 1, 'permiso_id' => 22],
            ['rol_id' => 1, 'permiso_id' => 23],
            ['rol_id' => 1, 'permiso_id' => 24],
            ['rol_id' => 1, 'permiso_id' => 25],
            ['rol_id' => 1, 'permiso_id' => 26],
            ['rol_id' => 1, 'permiso_id' => 27],
            ['rol_id' => 1, 'permiso_id' => 28],
            ['rol_id' => 1, 'permiso_id' => 29],
        ]);

        $this->call([
            EspecieSeeder::class,
            DenunciaTipoSeeder::class,
            ProcesoSeeder::class,
        ]);
    }
}
