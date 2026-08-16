<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * El contenido de la modal de adeudos ahora es visible para cualquiera
     * que vea el dashboard; el permiso solo controla quién puede registrar
     * (crear) los pagos, así que se renombra para reflejar eso.
     */
    public function up(): void
    {
        Permission::where('name', 'Ver adeudos')->update(['name' => 'Crear adeudos']);
    }

    public function down(): void
    {
        Permission::where('name', 'Crear adeudos')->update(['name' => 'Ver adeudos']);
    }
};
