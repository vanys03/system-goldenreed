<!-- Modal Editar AnyDesk -->
<div class="modal fade" id="modalEditarAnydesk{{ $anydesk->id }}" tabindex="-1"
    aria-labelledby="modalEditarAnydeskLabel{{ $anydesk->id }}" aria-hidden="true" translate="no">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('anydesks.update', $anydesk->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- ENCABEZADO --}}
                <div class="modal-header bg-gradient-dark border-bottom border-warning">
                    <h5 class="modal-title fw-bold d-flex align-items-center text-white"
                        id="modalEditarAnydeskLabel{{ $anydesk->id }}">
                        <i class="material-icons me-2 text-white">edit</i> Editar Acceso AnyDesk
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
                            <input type="text" name="nombre" class="form-control border-0" value="{{ $anydesk->nombre }}" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">Torre</label>
                        <div class="input-group border rounded">
                            <span class="input-group-text bg-light"><i class="material-icons text-secondary">cell_tower</i></span>
                            <input type="text" name="torre" class="form-control border-0" value="{{ $anydesk->torre }}" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">Código AnyDesk</label>
                        <div class="input-group border rounded">
                            <span class="input-group-text bg-light"><i class="material-icons text-secondary">tag</i></span>
                            <input type="text" name="codigo" class="form-control border-0" value="{{ $anydesk->codigo }}" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">Contraseña</label>
                        <div class="input-group border rounded">
                            <span class="input-group-text bg-light"><i class="material-icons text-secondary">lock</i></span>
                            <input type="password" name="contrasena" class="form-control border-0 anydesk-password-input" value="{{ $anydesk->contrasena }}" required>
                            <button type="button" class="btn btn-link text-secondary anydesk-toggle-password" tabindex="-1">
                                <span class="material-icons align-middle">visibility</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- PIE --}}
                <div class="modal-footer">
                    @can('Editar anydesks')
                    <button type="submit" class="btn btn-warning text-white">Actualizar</button>
                    @endcan
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>
