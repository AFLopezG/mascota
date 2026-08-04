<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vacunas', function (Blueprint $table) {
            $table->date('fecha_prox')->nullable()->after('fecha');
            $table->string('num_lote')->nullable()->after('lugar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vacunas', function (Blueprint $table) {
            $table->dropColumn(['fecha_prox', 'num_lote']);
        });
    }
};
