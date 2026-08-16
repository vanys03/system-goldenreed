<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permission = Permission::firstOrCreate([
            'name' => 'Ver adeudos',
            'guard_name' => 'web',
        ]);

        $superadmin = Role::where('name', 'Superadmin')->first();

        if ($superadmin) {
            $superadmin->givePermissionTo($permission);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Permission::where('name', 'Ver adeudos')->delete();
    }
};
