<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CampaniaTipoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('campania_tipos')->upsert([
            ['nombre' => 'Campañas de vacunación'],
            ['nombre' => 'Campañas de esterilización'],
            ['nombre' => 'Campañas de desparasitación'],
            ['nombre' => 'Campañas de empadronamiento']
        ], ['nombre'], [
            'created_at',
            'updated_at'
        ]);
    }
}
