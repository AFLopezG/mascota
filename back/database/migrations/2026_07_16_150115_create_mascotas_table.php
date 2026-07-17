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
            $table->string('foto')->nullable();
            $table->string('nombre');
            $table->string('tipo');
            $table->date('fec_nac')->nullable();
            $table->integer('edad')->nullable();
            $table->string('raza');
            $table->string('color');
            $table->string('tamano')->nullable();
            $table->double('peso')->nullable();
            $table->string('estado')->default('VIVO');
            $table->text('observacion')->nullable();
            $table->string('sexo');
            $table->string('categoria');
            $table->boolean('esterilizado')->default(false);
            $table->unsignedBigInteger('persona_id');
            $table->foreign('persona_id')->references('id')->on('personas');
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
