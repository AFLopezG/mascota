<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProcesoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('procesos')->upsert([
            ['id' => 1, 'orden' => 1, 'descripcion' => 'Recepcion de la denuncia'],
            ['id' => 2, 'orden' => 2, 'descripcion' => 'Verificacion en campo/ Notifiacion Autor'],
            ['id' => 3, 'orden' => 3, 'descripcion' => 'Observacion sanitaria del animal'],
            ['id' => 4, 'orden' => 4, 'descripcion' => 'Rescate animal'],
            ['id' => 5, 'orden' => 5, 'descripcion' => 'Entrega al propietario'],
            ['id' => 6, 'orden' => 6, 'descripcion' => 'Cierre del caso'],
        ], ['id'], ['orden', 'descripcion']);
    }
}
