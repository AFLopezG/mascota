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
        Schema::create('registro_vacunas', function (Blueprint $table) {
            $table->id();
            //persona   
            $table->string('cedula')->nullable();
            $table->string('nombre')->nullable();
            $table->string('domicilio')->nullable();
            $table->string('celular')->nullable();
            //mascota
            $table->string('nombre_mascota')->nullable();
            $table->boolean('menor')->default(false);
            $table->string('foto')->nullable();
            $table->string('lat')->nullable();
            $table->string('lng')->nullable();
            $table->dateTime('fecha_vacuna')->nullable();

            $table->unsignedBigInteger('campania_id');
            $table->foreign('campania_id')->references('id')->on('campanias');
            $table->unsignedBigInteger('especie_id');
            $table->foreign('especie_id')->references('id')->on('especies');
            $table->unsignedBigInteger('raza_id')->nullable();
            $table->foreign('raza_id')->references('id')->on('razas');
            $table->unsignedBigInteger('place_id');
            $table->foreign('place_id')->references('id')->on('places');
            $table->unsignedBigInteger('health_center_id');
            $table->foreign('health_center_id')->references('id')->on('health_centers');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registro_vacunas');
    }
};
