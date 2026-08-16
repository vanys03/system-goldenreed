@props(['matrix', 'role' => null])

@php
    $acciones = [
        'Ver' => 'visibility',
        'Crear' => 'add_circle',
        'Editar' => 'edit',
        'Eliminar' => 'delete_forever',
    ];
    $significados = [
        'Ver' => 'puede entrar y consultar',
        'Crear' => 'puede agregar registros nuevos',
        'Editar' => 'puede modificar los existentes',
        'Eliminar' => 'puede borrarlos',
    ];
    $rolePermisos = $role ? $role->permissions->pluck('name')->all() : [];
@endphp

<div class="alert alert-light border py-2 px-3 mb-2">
    <div class="d-flex flex-wrap column-gap-4 row-gap-1 small mb-1">
        @foreach($acciones as $accion => $icono)
            <span class="d-inline-flex align-items-center gap-1">
                <i class="material-icons text-secondary" style="font-size: 16px;">{{ $icono }}</i>
                <strong class="text-dark">{{ $accion }}</strong>
                <span class="text-secondary">= {{ $significados[$accion] }}</span>
            </span>
        @endforeach
    </div>
    <div class="small text-secondary">
        Marca el <strong>checkbox de la cabecera</strong> de una columna para aplicar esa acción a todos los módulos.
        Marca el checkbox <strong>"Todo"</strong> al final de una fila para dar acceso completo a ese módulo.
    </div>
</div>

<div class="border rounded-3" style="max-height: 50vh; overflow-y: auto;">
    <table class="table table-sm align-middle mb-0 permission-matrix">
        <thead>
            <tr>
                <th style="width: 24%; position: sticky; top: 0; background-color: #f8f9fa; z-index: 1;">
                    <div class="form-check mb-0 d-flex align-items-center gap-2">
                        <input class="form-check-input mt-0 perm-all-toggle" type="checkbox"
                            title="Dar acceso total: marca todo en todos los módulos">
                        <label class="form-check-label fw-bold text-dark">Módulo</label>
                    </div>
                </th>
                @foreach($acciones as $accion => $icono)
                    <th class="text-center" style="position: sticky; top: 0; background-color: #f8f9fa; z-index: 1;">
                        <div class="d-flex flex-column align-items-center gap-1">
                            <input class="form-check-input perm-col-toggle mt-0" type="checkbox"
                                data-accion="{{ $accion }}"
                                title="Marcar &quot;{{ $accion }}&quot; en todos los módulos">
                            <div class="d-flex align-items-center gap-1">
                                <i class="material-icons text-secondary" style="font-size: 16px;">{{ $icono }}</i>
                                <span class="fw-bold text-dark">{{ $accion }}</span>
                            </div>
                        </div>
                    </th>
                @endforeach
                <th class="text-center" style="width: 8%; position: sticky; top: 0; background-color: #f8f9fa; z-index: 1;">
                    Todo
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach($matrix as $fila)
                <tr>
                    <td class="text-dark text-sm">{{ $fila['label'] }}</td>
                    @foreach(array_keys($acciones) as $accion)
                        <td class="text-center">
                            @if(isset($fila['actions'][$accion]))
                                <input class="form-check-input perm-check" type="checkbox"
                                    name="permissions[]" value="{{ $fila['actions'][$accion] }}"
                                    data-accion="{{ $accion }}"
                                    title="{{ $accion }} {{ strtolower($fila['label']) }}"
                                    @checked(in_array($fila['actions'][$accion], $rolePermisos))>
                            @else
                                <span class="text-muted" title="Esta pantalla no tiene acción de {{ strtolower($accion) }}">—</span>
                            @endif
                        </td>
                    @endforeach
                    <td class="text-center">
                        <input class="form-check-input perm-row-toggle" type="checkbox"
                            title="Dar acceso completo al módulo {{ $fila['label'] }}">
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
