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
        Schema::create('mascotas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->date('fec_reg');
            $table->string('foto')->nullable();
            $table->string('nombre');
            $table->string('especie'); //especie
            $table->date('fec_nac')->nullable();
            $table->integer('edad')->nullable();
            $table->string('color_principal');
            $table->string('color_secundario')->nullable();
            $table->string('tamano')->nullable();
            $table->text('particular')->nullable();
            $table->double('peso')->nullable();
            $table->string('estado')->default('ACTIVO'); //ACTIVO, PERDIDO, ENCONTRADO, FALLECIDO, ADOPTADO OTRO
            $table->text('observacion')->nullable();
            $table->string('sexo');
            $table->boolean('esterilizado')->default(false);
            $table->date('fec_esterilizacion')->nullable();
            $table->unsignedBigInteger('campania_id')->nullable();
            $table->foreign('campania_id')->references('id')->on('campanias');
            $table->unsignedBigInteger('categoria_id')->nullable();
            $table->foreign('categoria_id')->references('id')->on('categorias');
            $table->unsignedBigInteger('persona_id');
            $table->foreign('persona_id')->references('id')->on('personas');
            $table->unsignedBigInteger('raza_id');
            $table->foreign('raza_id')->references('id')->on('razas');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mascotas');
    }
};
