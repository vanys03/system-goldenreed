<!-- Modal Editar AnyDesk -->
<div class="modal fade" id="modalEditarAnydesk{{ $anydesk->id }}" tabindex="-1"
    aria-labelledby="modalEditarAnydeskLabel{{ $anydesk->id }}" aria-hidden="true" translate="no">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form class="anydesk-form" action="{{ route('anydesks.update', $anydesk->id) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- ENCABEZADO --}}
                <div class="modal-header border-0 pb-0">
                    <div class="d-flex align-items-center">
                        <div class="icon-shape-sm rounded-circle bg-light d-flex align-items-center justify-content-center me-3">
                            <i class="material-icons text-secondary">desktop_windows</i>
                        </div>
                        <div>
                            <p class="text-xs text-secondary text-uppercase mb-0">Editar acceso AnyDesk</p>
                            <h6 class="fw-bold mb-0" id="modalEditarAnydeskLabel{{ $anydesk->id }}">
                                {{ $anydesk->torre }}
                            </h6>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                {{-- CUERPO --}}
                <div class="modal-body px-4 pb-2 pt-3">

                    <h6 class="text-xs text-secondary text-uppercase fw-bold mb-2">Datos del acceso</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="text-secondary text-uppercase text-xs d-block mb-1">Torre</label>
                            <input type="text" name="torre" class="form-control form-control-sm border rounded-2"
                                value="{{ $anydesk->torre }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="text-secondary text-uppercase text-xs d-block mb-1">Código AnyDesk</label>
                            <input type="text" name="codigo" class="form-control form-control-sm border rounded-2"
                                value="{{ $anydesk->codigo }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="text-secondary text-uppercase text-xs d-block mb-1">Contraseña</label>
                            <div class="anydesk-campo-pass">
                                <input type="password" name="contrasena"
                                    class="form-control form-control-sm border rounded-2 anydesk-password-input"
                                    value="{{ $anydesk->contrasena }}" required>
                                <button type="button" class="btn btn-link text-secondary anydesk-toggle-password"
                                    tabindex="-1" title="Mostrar/ocultar contraseña">
                                    <span class="material-icons align-middle">visibility</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">

                    {{-- Imágenes --}}
                    <div class="anydesk-panel p-3 mb-3 rounded-3">
                        <div class="d-flex align-items-start gap-3">
                            <i class="material-icons text-secondary">photo_library</i>
                            <div class="flex-grow-1 anydesk-bloque-imagenes">
                                <label class="text-secondary text-uppercase text-xs fw-bold d-block mb-2">
                                    Imágenes
                                    @if ($anydesk->imagenes->isNotEmpty())
                                        <span class="text-secondary fw-normal">({{ $anydesk->imagenes->count() }})</span>
                                    @endif
                                </label>

                                @if ($anydesk->imagenes->isEmpty())
                                    <div class="anydesk-sin-imagenes d-flex align-items-center px-2 py-1 rounded-2 mb-3 text-xs fw-bold">
                                        <i class="material-icons me-1" style="font-size:16px;">info</i>
                                        Este acceso todavía no tiene imágenes.
                                    </div>
                                @else
                                    <div class="d-flex flex-wrap gap-3 mb-2">
                                        @foreach ($anydesk->imagenes as $imagen)
                                            <div class="anydesk-imagen-guardada text-center">
                                                <a href="{{ route('anydesks.imagen', $imagen->id) }}" target="_blank" rel="noopener">
                                                    <img src="{{ route('anydesks.imagen', $imagen->id) }}"
                                                        alt="Imagen del acceso" class="rounded-2 border"
                                                        style="width: 96px; height: 96px; object-fit: cover;" loading="lazy">
                                                </a>
                                                <div class="form-check d-flex align-items-center justify-content-center gap-1 mt-1 mb-0">
                                                    <input class="form-check-input mt-0 anydesk-marcar-borrado" type="checkbox"
                                                        name="eliminar_imagenes[]" value="{{ $imagen->id }}"
                                                        id="borrarImagen{{ $imagen->id }}">
                                                    <label class="form-check-label text-danger anydesk-quitar mb-0"
                                                        for="borrarImagen{{ $imagen->id }}">Quitar</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <p class="text-xs text-secondary mb-3">Las imágenes marcadas se borran al guardar los cambios.</p>
                                @endif

                                <label class="text-secondary text-uppercase text-xs d-block mb-1">Agregar imágenes</label>
                                <input type="file" name="imagenes[]"
                                    class="form-control form-control-sm border rounded-2 anydesk-input-imagenes"
                                    accept="image/jpeg,image/png,image/webp" multiple>
                                <small class="text-secondary">Hasta 8 por envío. JPG, PNG o WEBP, máximo 4 MB cada una.</small>
                                <div class="anydesk-preview d-flex flex-wrap gap-2 mt-3"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- PIE --}}
                <div class="modal-footer justify-content-between border-0 px-4 pb-4 pt-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    @can('Editar anydesks')
                        <button type="submit" class="btn btn-warning">
                            <i class="material-icons align-middle" style="font-size:18px;">save</i> Actualizar
                        </button>
                    @endcan
                </div>
            </form>
        </div>
    </div>
</div>
