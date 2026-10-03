<!-- Modal Crear AnyDesk -->
<div class="modal fade" id="modalCrearAnydesk" tabindex="-1" aria-labelledby="modalCrearAnydeskLabel"
    aria-hidden="true" translate="no">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form class="anydesk-form" action="{{ route('anydesks.store') }}" method="POST"
                enctype="multipart/form-data">
                @csrf

                {{-- ENCABEZADO --}}
                <div class="modal-header border-0 pb-0">
                    <div class="d-flex align-items-center">
                        <div class="icon-shape-sm rounded-circle bg-light d-flex align-items-center justify-content-center me-3">
                            <i class="material-icons text-secondary">desktop_windows</i>
                        </div>
                        <div>
                            <p class="text-xs text-secondary text-uppercase mb-0">Nuevo registro</p>
                            <h6 class="fw-bold mb-0" id="modalCrearAnydeskLabel">Registrar acceso AnyDesk</h6>
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
                                placeholder="Ej. Torre 1" required>
                        </div>

                        <div class="col-md-4">
                            <label class="text-secondary text-uppercase text-xs d-block mb-1">Código AnyDesk</label>
                            <input type="text" name="codigo" class="form-control form-control-sm border rounded-2"
                                placeholder="Ej. 123 456 789" required>
                        </div>

                        <div class="col-md-4">
                            <label class="text-secondary text-uppercase text-xs d-block mb-1">Contraseña</label>
                            <div class="anydesk-campo-pass">
                                <input type="password" name="contrasena"
                                    class="form-control form-control-sm border rounded-2 anydesk-password-input"
                                    value="{{ \App\Models\Anydesk::CONTRASENA_POR_DEFECTO }}" required>
                                <button type="button" class="btn btn-link text-secondary anydesk-toggle-password"
                                    tabindex="-1" title="Mostrar/ocultar contraseña">
                                    <span class="material-icons align-middle">visibility</span>
                                </button>
                            </div>
                            <small class="text-secondary">Viene la estándar; puedes cambiarla.</small>
                        </div>
                    </div>

                    <hr class="my-3">

                    {{-- Imágenes --}}
                    <div class="anydesk-panel p-3 mb-3 rounded-3">
                        <div class="d-flex align-items-start gap-3">
                            <i class="material-icons text-secondary">photo_library</i>
                            <div class="flex-grow-1 anydesk-bloque-imagenes">
                                <label class="text-secondary text-uppercase text-xs fw-bold d-block mb-2">Imágenes</label>

                                <input type="file" name="imagenes[]"
                                    class="form-control form-control-sm border rounded-2 anydesk-input-imagenes"
                                    accept="image/jpeg,image/png,image/webp" multiple>
                                <small class="text-secondary">
                                    Hasta 8 por envío, puedes agregar más después. JPG, PNG o WEBP, máximo 4 MB cada una.
                                </small>
                                <div class="anydesk-preview d-flex flex-wrap gap-2 mt-3"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- PIE --}}
                <div class="modal-footer justify-content-between border-0 px-4 pb-4 pt-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="material-icons align-middle" style="font-size:18px;">check_circle</i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
