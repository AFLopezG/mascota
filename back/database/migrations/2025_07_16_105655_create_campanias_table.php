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
        Schema::create('campanias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->date('fec_ini');
            $table->date('fec_fin');
            $table->string('lugar');
            $table->string('descripcion')->nullable();
            $table->string('estado')->nullable();
            $table->unsignedBigInteger('campania_tipo_id');
            $table->foreign('campania_tipo_id')->references('id')->on('campania_tipos');
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
        Schema::dropIfExists('campanias');
    }
};
