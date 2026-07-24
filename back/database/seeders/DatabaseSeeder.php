<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
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
            ['id' => 8, 'nombre' => 'Modificar Contraseña', 'permiso_id' => 2],
            ['id' => 9, 'nombre' => 'Activar Usuario', 'permiso_id' => 2]
        ]);
        DB::table('users')->insert([
            'cedula' => '1234567890',
            'name' => 'Admin',
            'nombre' => 'Administrador',
            'celular' => '1234567890',
            'fecha_limite' => '2999-12-31',
            'estado' => 'ACTIVO',
            'email' => 'Admin@test.com',
            'password' => Hash::make( 'admin123Admin'), // password
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
        ]);

        $this->call([
            EspecieSeeder::class,
        ]);

    }
}
