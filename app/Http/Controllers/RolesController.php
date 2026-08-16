<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RolesController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Ver roles')->only(['index', 'show']);
        $this->middleware('permission:Crear roles')->only(['create', 'store']);
        $this->middleware('permission:Editar roles')->only(['edit', 'update']);
        $this->middleware('permission:Eliminar roles')->only('destroy');
    }

    public function index()
{
    $roles = Role::with('permissions')->get(['id', 'name']);

    // Ordena Superadmin primero
    $roles = $roles->sortByDesc(function ($role) {
        return strtolower(trim($role->name)) === 'superadmin';
    })->values(); // Reindexa para evitar claves no secuenciales

    $matrix = $this->permissionMatrix();

    return view('roles.index', compact('roles', 'matrix'));
}


    public function create()
    {
        $matrix = $this->permissionMatrix();
        return view('roles.create', compact('matrix'));
    }

    /**
     * Agrupa todos los permisos en filas por módulo (a partir del nombre "Accion modulo")
     * y columnas por acción, para pintar la matriz de permisos en las vistas de roles.
     * Se deriva dinámicamente: cualquier permiso nuevo con el formato "Accion modulo"
     * aparece solo con crearlo, sin tocar las vistas.
     */
    private function permissionMatrix(): array
    {
        $labels = [
            'actividades' => 'Actividades/Accesos',
            'auditoria' => 'Auditoría',
            'clientes rentas' => 'Clientes de rentas',
            'telefonos' => 'Teléfonos',
            'anydesks' => 'AnyDesk',
            'adeudos' => 'Adeudos (dashboard)',
        ];

        $matrix = [];
        foreach (Permission::orderBy('id')->get(['id', 'name']) as $permission) {
            [$accion, $modulo] = array_pad(explode(' ', $permission->name, 2), 2, '');

            if (!isset($matrix[$modulo])) {
                $matrix[$modulo] = [
                    'label' => $labels[$modulo] ?? ucfirst($modulo),
                    'actions' => [],
                ];
            }

            $matrix[$modulo]['actions'][$accion] = $permission->name;
        }

        return array_values($matrix);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'array'
        ]);

        $role = Role::create(['name' => $data['name']]);
        if (!empty($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return redirect()->route('roles.index')->with('success', 'Rol creado exitosamente.');
    }

    public function edit($id)
    {
        $role = Role::with('permissions')->findOrFail($id, ['id', 'name']);
        $matrix = $this->permissionMatrix();
        return view('roles.edit', compact('role', 'matrix'));
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|unique:roles,name,' . $id,
            'permissions' => 'array'
        ]);

        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('roles.index')->with('success', 'Rol actualizado.');
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        $usuariosConRol = DB::table('model_has_roles')
            ->where('role_id', $role->id)
            ->exists();

        if ($usuariosConRol) {
            return redirect()->route('roles.index')->with('error', 'No puedes eliminar un rol que está asignado a usuarios.');
        }

        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Rol eliminado correctamente.');
    }
}
