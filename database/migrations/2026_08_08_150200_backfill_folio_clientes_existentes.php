<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('UPDATE clientes SET folio = id WHERE folio IS NULL');
    }

    public function down(): void
    {
        // No-op: no reconstruimos el estado "sin folio" de los clientes viejos.
    }
};
