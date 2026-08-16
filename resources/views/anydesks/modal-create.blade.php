<!-- Modal Crear AnyDesk -->
<div class="modal fade" id="modalCrearAnydesk" tabindex="-1" aria-labelledby="modalCrearAnydeskLabel"
    aria-hidden="true" translate="no">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('anydesks.store') }}" method="POST">
                @csrf

                {{-- ENCABEZADO --}}
                <div class="modal-header bg-gradient-dark border-bottom border-primary">
                    <h5 class="modal-title fw-bold d-flex align-items-center text-white" id="modalCrearAnydeskLabel">
                        <i class="material-icons me-2 text-white">desktop_windows</i> Registrar Acceso AnyDesk
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"
                        style="filter: invert(1);"></button>
                </div>

                {{-- CUERPO --}}
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">Nombre</label>
                        <div class="input-group border rounded">
                            <span class="input-group-text bg-light"><i class="material-icons text-secondary">badge</i></span>
                            <input type="text" name="nombre" class="form-control border-0" placeholder="Ej. PC Torre Norte" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">Torre</label>
                        <div class="input-group border rounded">
                            <span class="input-group-text bg-light"><i class="material-icons text-secondary">cell_tower</i></span>
                            <input type="text" name="torre" class="form-control border-0" placeholder="Ej. Torre 1" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">Código AnyDesk</label>
                        <div class="input-group border rounded">
                            <span class="input-group-text bg-light"><i class="material-icons text-secondary">tag</i></span>
                            <input type="text" name="codigo" class="form-control border-0" placeholder="Ej. 123 456 789" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">Contraseña</label>
                        <div class="input-group border rounded">
                            <span class="input-group-text bg-light"><i class="material-icons text-secondary">lock</i></span>
                            <input type="password" name="contrasena" class="form-control border-0 anydesk-password-input" placeholder="Contraseña de acceso" required>
                            <button type="button" class="btn btn-link text-secondary anydesk-toggle-password" tabindex="-1">
                                <span class="material-icons align-middle">visibility</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- PIE --}}
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
