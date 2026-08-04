<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('categorias')->upsert([
            ['nombre' => 'Animal doméstico'],
            ['nombre' => 'Animal comunitario'],
            ['nombre' => 'Animal callejero'],
            ['nombre' => 'Animal rescatado'],
            ['nombre' => 'Animal potencialmente peligroso']            
        ], ['nombre'], [
            'created_at',
            'updated_at'    
        ]);
    }
}
