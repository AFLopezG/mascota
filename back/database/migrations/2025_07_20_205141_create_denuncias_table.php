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
        Schema::create('denuncias', function (Blueprint $table) {
            $table->id();
            $table->integer('numero');
            $table->dateTime('fec_denuncia');
            $table->string('direccion')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('zona')->nullable();
            $table->string('color')->nullable();
            $table->string('tamanio')->nullable();
            $table->string('estado')->nullable();
            $table->text('observacion')->nullable();

            $table->string('nom_afectado');
            $table->string('edad');
            $table->string('telefono');
            $table->string('dir_inicidente');
            $table->string('tipo_lesion');
            $table->string('dias_obser');
            $table->string('resultado');
            $table->string('obs');
            


            $table->unsignedBigInteger('raza_id');
            $table->foreign('raza_id')->references('id')->on('razas');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->unsignedBigInteger('persona_id');
            $table->foreign('persona_id')->references('id')->on('personas');
            $table->unsignedBigInteger('mascota_id');
            $table->foreign('mascota_id')->references('id')->on('mascotas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('denuncias');
    }
};
