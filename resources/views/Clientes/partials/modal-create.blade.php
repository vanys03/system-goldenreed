@push('styles')
<style>
    #modalCrearCliente .icon-shape-sm {
        width: 40px;
        height: 40px;
        min-width: 40px;
    }

    #modalCrearCliente .form-label-sm {
        font-size: 0.7rem;
        letter-spacing: .03em;
    }

    #modalCrearCliente .form-control,
    #modalCrearCliente .form-select {
        border-color: #dee2e6;
    }

    #modalCrearCliente .form-control:focus,
    #modalCrearCliente .form-select:focus {
        border-color: #adb5bd;
        box-shadow: none;
    }
</style>
@endpush

<!-- Modal Crear Cliente -->
<div class="modal fade" id="modalCrearCliente" tabindex="-1" aria-labelledby="modalCrearClienteLabel" aria-hidden="true"
  translate="no">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <form action="{{ route('clientes.store') }}" method="POST">
        @csrf

        {{-- ENCABEZADO --}}
        <div class="modal-header border-0 pb-0">
          <div class="d-flex align-items-center">
            <div class="icon-shape-sm rounded-circle bg-light d-flex align-items-center justify-content-center me-3">
              <i class="material-icons text-secondary">person_add</i>
            </div>
            <div>
              <p class="text-xs text-secondary text-uppercase mb-0">Nuevo registro</p>
              <h6 class="fw-bold mb-0" id="modalCrearClienteLabel">Registrar cliente</h6>
            </div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <!-- CUERPO -->
        <div class="modal-body px-4 pb-2 pt-3">

          {{-- Folio del contrato impreso --}}
          <div class="p-3 mb-4 rounded-3" style="background:#f8f9fa;">
            <div class="d-flex align-items-start gap-3">
              <i class="material-icons text-secondary">confirmation_number</i>
              <div class="flex-grow-1">
                <label for="select-folio-cliente" class="text-secondary text-uppercase text-xs fw-bold d-block mb-1">
                  Folio del contrato
                </label>
                <select name="folio_id" id="select-folio-cliente" class="form-select form-select-sm border rounded-2" required
                  {{ $folios->isEmpty() ? 'disabled' : '' }}>
                  <option value="">-- Selecciona un folio --</option>
                  @foreach ($folios as $folio)
                    <option value="{{ $folio->id }}">
                      GD{{ sprintf('%04d', $folio->numero) }}
                      @if ($folio->impreso_at)
                        — impreso {{ $folio->impreso_at->format('d/m/Y H:i') }}
                      @endif
                    </option>
                  @endforeach
                </select>
                <div id="sin-folios-disponibles" class="text-danger text-xs mt-1 {{ $folios->isEmpty() ? '' : 'd-none' }}">
                  No hay folios disponibles. Imprime un contrato en blanco primero.
                </div>
              </div>
            </div>

            <div class="form-check mt-2 ps-4 ms-1">
              <input class="form-check-input" type="checkbox" name="sin_folio" value="1" id="check-sin-folio">
              <label class="form-check-label text-xs text-secondary" for="check-sin-folio">
                No tengo un folio impreso — asignar el siguiente folio de la numeración automáticamente
              </label>
            </div>
          </div>

          {{-- Fecha contrato (automática) --}}
          <input type="hidden" name="fecha_contrato" value="{{ now()->toDateString() }}">

          <h6 class="text-xs text-secondary text-uppercase fw-bold mb-2">Datos del cliente</h6>
          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label class="text-secondary text-uppercase text-xs d-block mb-1">Nombre</label>
              <input type="text" name="nombre" class="form-control form-control-sm border rounded-2"
                placeholder="Ej. Ana Martínez" required>
            </div>

            <div class="col-md-4">
              <label class="text-secondary text-uppercase text-xs d-block mb-1">Teléfono 1</label>
              <input type="text" name="telefono1" class="form-control form-control-sm border rounded-2"
                placeholder="Ej. 5551234567">
            </div>

            <div class="col-md-4">
              <label class="text-secondary text-uppercase text-xs d-block mb-1">Teléfono 2</label>
              <input type="text" name="telefono2" class="form-control form-control-sm border rounded-2"
                placeholder="Opcional">
            </div>

            <div class="col-md-4">
              <label class="text-secondary text-uppercase text-xs d-block mb-1">Día de cobro</label>
              <input type="number" name="dia_cobro" class="form-control form-control-sm border rounded-2" min="1"
                max="31" placeholder="Ej. 15" required>
            </div>

            <div class="col-md-8">
              <label class="text-secondary text-uppercase text-xs d-block mb-1">Paquete</label>
              <select name="paquete_id" class="form-select form-select-sm border rounded-2" required>
                <option value="">-- Selecciona un paquete --</option>
                @foreach($paquetes as $paquete)
                  <option value="{{ $paquete->id }}">
                    {{ $paquete->nombre }} - ${{ number_format($paquete->precio, 2) }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>

          <hr class="my-3">

          <h6 class="text-xs text-secondary text-uppercase fw-bold mb-2">Instalación</h6>
          <div class="row g-3 mb-3">
            <div class="col-md-3">
              <label class="text-secondary text-uppercase text-xs d-block mb-1">Torre</label>
              <input type="text" name="torre" class="form-control form-control-sm border rounded-2">
            </div>

            <div class="col-md-3">
              <label class="text-secondary text-uppercase text-xs d-block mb-1">Panel</label>
              <input type="text" name="panel" class="form-control form-control-sm border rounded-2">
            </div>

            <div class="col-md-3">
              <label class="text-secondary text-uppercase text-xs d-block mb-1">Dirección MAC</label>
              <input type="text" name="Mac" class="form-control form-control-sm border rounded-2"
                placeholder="A1:B2:C3:D4:E5:F6">
            </div>

            <div class="col-md-3">
              <label class="text-secondary text-uppercase text-xs d-block mb-1">Dirección IP</label>
              <input type="text" name="IP" class="form-control form-control-sm border rounded-2"
                placeholder="192.168.1.1">
            </div>

            <div class="col-md-6">
              <label class="text-secondary text-uppercase text-xs d-block mb-1">Dirección</label>
              <input type="text" name="direccion" class="form-control form-control-sm border rounded-2"
                placeholder="Ej. Calle 123, Colonia Centro">
            </div>

            <div class="col-md-6">
              <label class="text-secondary text-uppercase text-xs d-block mb-1">Coordenadas</label>
              <input type="text" name="coordenadas" class="form-control form-control-sm border rounded-2"
                placeholder="Ej. 19.4326,-99.1332">
            </div>

            <div class="col-12">
              <label class="text-secondary text-uppercase text-xs d-block mb-1">Referencias</label>
              <textarea name="referencias" class="form-control form-control-sm border rounded-2" rows="2"
                placeholder="Casa azul frente a la tienda."></textarea>
            </div>
          </div>
        </div>

        {{-- PIE DE MODAL --}}
        <div class="modal-footer justify-content-between border-0 px-4 pb-4 pt-2">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success" id="btn-guardar-cliente" {{ $folios->isEmpty() ? 'disabled' : '' }}>
            <i class="material-icons align-middle" style="font-size:18px;">check_circle</i> Guardar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
