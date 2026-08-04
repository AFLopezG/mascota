<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DenunciaTipoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('denuncia_tipos')->upsert([
            ['id' => 1, 'nombre' => 'Mordedura'],
            ['id' => 2, 'nombre' => 'Maltrato animal'],
            ['id' => 3, 'nombre' => 'Abandono'],
            ['id' => 4, 'nombre' => 'Animal agresivo'],
            ['id' => 5, 'nombre' => 'Animal suelto'],
            ['id' => 6, 'nombre' => 'Ruidos molestos'],
        ], ['id'], ['nombre']);
    }
}
