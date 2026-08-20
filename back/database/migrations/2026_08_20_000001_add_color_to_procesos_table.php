<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procesos', function (Blueprint $table) {
            $table->string('color', 32)->default('primary')->after('descripcion');
        });

        $defaults = [
            1 => 'orange',
            2 => 'primary',
            3 => 'teal',
            4 => 'deep-orange',
            5 => 'purple',
            6 => 'positive',
        ];

        foreach ($defaults as $order => $color) {
            DB::table('procesos')
                ->where('orden', $order)
                ->update(['color' => $color]);
        }
    }

    public function down(): void
    {
        Schema::table('procesos', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
