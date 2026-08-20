<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProcesoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('procesos')->upsert([
            ['id' => 1, 'orden' => 1, 'descripcion' => 'Recepcion de la denuncia', 'color' => 'orange'],
            ['id' => 2, 'orden' => 2, 'descripcion' => 'Verificacion en campo/ Notifiacion Autor', 'color' => 'primary'],
            ['id' => 3, 'orden' => 3, 'descripcion' => 'Observacion sanitaria del animal', 'color' => 'teal'],
            ['id' => 4, 'orden' => 4, 'descripcion' => 'Rescate animal', 'color' => 'deep-orange'],
            ['id' => 5, 'orden' => 5, 'descripcion' => 'Entrega al propietario', 'color' => 'purple'],
            ['id' => 6, 'orden' => 6, 'descripcion' => 'Cierre del caso', 'color' => 'positive'],
        ], ['id'], ['orden', 'descripcion', 'color']);
    }
}
