<!-- Modal Crear Rol -->
<div class="modal fade" id="modalCrearRol" tabindex="-1" aria-labelledby="modalCrearRolLabel" aria-hidden="true"
  translate="no">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <form class="roles-form" action="{{ route('roles.store') }}" method="POST">
        @csrf

        {{-- ENCABEZADO --}}
        <div class="modal-header border-0 pb-0">
          <div class="d-flex align-items-center">
            <div class="icon-shape-sm rounded-circle bg-light d-flex align-items-center justify-content-center me-3">
              <i class="material-icons text-secondary">admin_panel_settings</i>
            </div>
            <div>
              <p class="text-xs text-secondary text-uppercase mb-0">Nuevo registro</p>
              <h6 class="fw-bold mb-0" id="modalCrearRolLabel">Crear rol</h6>
            </div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        {{-- CUERPO --}}
        <div class="modal-body px-4 pb-2 pt-3">

          <h6 class="text-xs text-secondary text-uppercase fw-bold mb-2">Datos del rol</h6>
          <div class="row g-3 mb-3">
            <div class="col-md-5">
              <label class="text-secondary text-uppercase text-xs d-block mb-1">Nombre del rol</label>
              <input type="text" name="name" class="form-control form-control-sm border rounded-2"
                placeholder="Ej. Administrador" required>
            </div>
          </div>

          <hr class="my-3">

          <h6 class="text-xs text-secondary text-uppercase fw-bold mb-2">Permisos</h6>
          <x-permission-matrix :matrix="$matrix" />
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
