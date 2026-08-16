<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // El nuevo flujo de folios (impresión → tabla folios → asignación al registrar
        // cliente) es independiente de los folios que ya traían los clientes existentes
        // en clientes.folio. Arranca desde 0 sin importar esos valores previos.
        DB::table('contadores')
            ->where('nombre', 'folio_cliente')
            ->update(['valor' => 0]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: no hay forma segura de reconstruir el valor anterior del contador.
    }
};
