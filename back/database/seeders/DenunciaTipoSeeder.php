<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DenunciaTipoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \DB::table('denuncia_tipos')->upsert([
            ['id' => 1, 'nombre' => 'Mordedura'],
            ['id' => 2, 'nombre' => 'Agresion'],
            ['id' => 3, 'nombre' => 'Animal suelto'],
            ['id' => 4, 'nombre' => 'Maltrato'],
            ['id' => 5, 'nombre' => 'Otro'],
        ], ['id'], ['nombre']);
    }
}
