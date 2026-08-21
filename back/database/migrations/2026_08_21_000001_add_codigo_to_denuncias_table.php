<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('denuncias', function (Blueprint $table) {
            $table->string('codigo')->nullable()->after('numero');
        });

        $denuncias = DB::table('denuncias')
            ->select('id', 'numero', 'fec_denuncia')
            ->orderBy('fec_denuncia')
            ->orderBy('id')
            ->get();

        foreach ($denuncias as $denuncia) {
            $gestion = Carbon::parse($denuncia->fec_denuncia)->year;
            DB::table('denuncias')
                ->where('id', $denuncia->id)
                ->update([
                    'codigo' => $gestion . '-' . $denuncia->numero,
                ]);
        }

        Schema::table('denuncias', function (Blueprint $table) {
            $table->unique('codigo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('denuncias', function (Blueprint $table) {
            $table->dropUnique('denuncias_codigo_unique');
            $table->dropColumn('codigo');
        });
    }
};
