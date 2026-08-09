<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contadores', function (Blueprint $table) {
            $table->string('nombre')->primary();
            $table->unsignedInteger('valor')->default(0);
            $table->timestamps();
        });

        DB::table('contadores')->insert([
            'nombre' => 'folio_cliente',
            'valor' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('contadores');
    }
};
