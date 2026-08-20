<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->dateTime('fechaHora');
            $table->string('actividad');
            $table->string('resultado');
            $table->text('obser')->nullable();
            $table->unsignedBigInteger('denuncia_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('proceso_id');
            $table->unsignedBigInteger('personal_id')->nullable();
            $table->timestamps();

            $table->foreign('denuncia_id')->references('id')->on('denuncias')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('proceso_id')->references('id')->on('procesos');
            $table->foreign('personal_id')->references('id')->on('personals');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
