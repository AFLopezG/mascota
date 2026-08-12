<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registro_vacunas', function (Blueprint $table) {
            $table->dropForeign(['raza_id']);
        });

        Schema::table('registro_vacunas', function (Blueprint $table) {
            $table->unsignedBigInteger('raza_id')->nullable()->change();
            $table->foreign('raza_id')->references('id')->on('razas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('registro_vacunas', function (Blueprint $table) {
            $table->dropForeign(['raza_id']);
        });

        Schema::table('registro_vacunas', function (Blueprint $table) {
            $table->unsignedBigInteger('raza_id')->nullable(false)->change();
            $table->foreign('raza_id')->references('id')->on('razas');
        });
    }
};
