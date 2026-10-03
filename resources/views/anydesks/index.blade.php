<x-layout bodyClass="g-sidenav-show bg-gray-200">
    <x-navbars.sidebar activePage='anydesks' />

    @include('anydesks.partials.estilos')

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg" translate="no">
        <!-- Navbar -->
        <x-navbars.navs.auth titlePage="AnyDesk" />
        <!-- End Navbar -->

        <div class="card m-4">
            <div class="table-responsive p-3">
                <div class="d-flex justify-content-between align-items-center px-3 pt-3">
                    <h5 class="mb-0">Accesos AnyDesk por torre</h5>
                    @can('Crear anydesks')
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearAnydesk">
                            <span class="material-icons align-middle">add</span> Agregar AnyDesk
                        </button>
                    @endcan
                </div>

                <table id="tabla-anydesks" class="table align-items-center mb-0 w-100 d-none">
                    <thead>
                        <tr>
                            <th class="text-uppercase text-dark text-xs font-weight-bolder">Imágenes</th>
                            <th class="text-uppercase text-dark text-xs font-weight-bolder ps-2">Torre</th>
                            <th class="text-uppercase text-dark text-xs font-weight-bolder ps-2">Código</th>
                            <th class="text-uppercase text-dark text-xs font-weight-bolder ps-2">Contraseña</th>
                            <th class="text-uppercase text-dark text-xs font-weight-bolder ps-2 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($anydesks as $anydesk)
                            @php
                                $imagenes = $anydesk->imagenes;
                                $visibles = $imagenes->take(3);
                                $restantes = $imagenes->count() - $visibles->count();
                                $urls = $imagenes->map(function ($img) {
                                    return route('anydesks.imagen', $img->id);
                                })->values();
                                $tituloGaleria = $anydesk->torre . ' — ' . $anydesk->codigo;
                            @endphp
                            <tr>
                                <td>
                                    @if ($imagenes->isEmpty())
                                        <span class="text-secondary d-flex align-items-center gap-1 text-xs px-2 py-1">
                                            <span class="material-icons" style="font-size: 18px;">image_not_supported</span>
                                            Sin imágenes
                                        </span>
                                    @else
                                        <div class="d-flex align-items-center gap-1 px-2 py-1">
                                            @foreach ($visibles as $i => $imagen)
                                                <img src="{{ route('anydesks.imagen', $imagen->id) }}"
                                                    alt="Imagen del acceso de {{ $anydesk->torre }}"
                                                    class="rounded border anydesk-thumb"
                                                    style="width: 44px; height: 44px; object-fit: cover; cursor: zoom-in;"
                                                    loading="lazy"
                                                    data-galeria="{{ $urls->toJson() }}"
                                                    data-indice="{{ $i }}"
                                                    data-titulo="{{ $tituloGaleria }}">
                                            @endforeach

                                            @if ($restantes > 0)
                                                <span class="badge bg-gradient-dark anydesk-thumb" style="cursor: zoom-in;"
                                                    data-galeria="{{ $urls->toJson() }}"
                                                    data-indice="{{ $visibles->count() }}"
                                                    data-titulo="{{ $tituloGaleria }}">
                                                    +{{ $restantes }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <p class="text-xs font-weight-bold mb-0">{{ $anydesk->torre }}</p>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="text-xs font-weight-normal mb-0 anydesk-copy-text">{{ $anydesk->codigo }}</span>
                                        <button type="button" class="btn btn-link p-0 text-secondary anydesk-copy" data-copy="{{ $anydesk->codigo }}" title="Copiar código">
                                            <span class="material-icons" style="font-size: 16px;">content_copy</span>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="text-xs font-weight-normal mb-0 anydesk-secret" data-value="{{ $anydesk->contrasena }}">••••••••</span>
                                        <button type="button" class="btn btn-link p-0 text-secondary anydesk-toggle-secret" title="Mostrar/ocultar contraseña">
                                            <span class="material-icons" style="font-size: 16px;">visibility</span>
                                        </button>
                                        <button type="button" class="btn btn-link p-0 text-secondary anydesk-copy" data-copy="{{ $anydesk->contrasena }}" title="Copiar contraseña">
                                            <span class="material-icons" style="font-size: 16px;">content_copy</span>
                                        </button>
                                    </div>
                                </td>
                                <td class="align-middle text-center">
                                    @can('Editar anydesks')
                                        <button class="btn btn-link text-success p-0 mx-1" title="Editar"
                                            data-bs-toggle="modal" data-bs-target="#modalEditarAnydesk{{ $anydesk->id }}">
                                            <span class="material-icons">edit</span>
                                        </button>
                                    @endcan

                                    @can('Eliminar anydesks')
                                        <button type="button" class="btn btn-link text-danger p-0 mx-1" title="Eliminar"
                                            data-bs-toggle="modal" data-bs-target="#modalEliminarAnydesk{{ $anydesk->id }}">
                                            <span class="material-icons">delete_forever</span>
                                        </button>
                                    @endcan

                                    <!-- Modales -->
                                    @include('anydesks.modal-edit', ['anydesk' => $anydesk])
                                    @include('anydesks.modal-delete', ['anydesk' => $anydesk])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal para crear AnyDesk -->
        @include('anydesks.modal-create')

        <!-- Visor de imágenes -->
        @include('anydesks.modal-visor')

        <!-- Alertas -->
        @include('components.alert-toast')

        @include('anydesks.partials.scripts')

    </main>
</x-layout>
