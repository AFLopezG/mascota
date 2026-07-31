<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProcesoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('procesos')->upsert([
            ['id' => 1, 'orden' => 1, 'descripcion' => 'Recepcionada'],
            ['id' => 2, 'orden' => 2, 'descripcion' => 'En revision'],
            ['id' => 3, 'orden' => 3, 'descripcion' => 'En seguimiento'],
            ['id' => 4, 'orden' => 4, 'descripcion' => 'Finalizada'],
        ], ['id'], ['orden', 'descripcion']);
    }
}
