<style>
    #formEditarCliente .icon-shape-sm {
        width: 40px;
        height: 40px;
        min-width: 40px;
    }

    #formEditarCliente .form-label-sm {
        font-size: 0.7rem;
        letter-spacing: .03em;
    }

    #formEditarCliente .form-control,
    #formEditarCliente .form-select {
        border-color: #dee2e6;
    }

    #formEditarCliente .form-control:focus,
    #formEditarCliente .form-select:focus {
        border-color: #adb5bd;
        box-shadow: none;
    }

    #formEditarCliente .doc-existente {
        background: #e7f7ed;
        border: 1px solid #b7e4c7;
        color: #157347;
    }
</style>

<form id="formEditarCliente" action="{{ route('clientes.update', $cliente->id) }}" method="POST"
  enctype="multipart/form-data">
  @csrf
  @method('PUT')

  {{-- ENCABEZADO --}}
  <div class="modal-header border-0 pb-0">
    <div class="d-flex align-items-center">
      <div class="icon-shape-sm rounded-circle bg-light d-flex align-items-center justify-content-center me-3">
        <i class="material-icons text-secondary">edit</i>
      </div>
      <div>
        <p class="text-xs text-secondary text-uppercase mb-0">Editar registro</p>
        <h6 class="fw-bold mb-0">{{ $cliente->nombre }}</h6>
      </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
  </div>

  {{-- CUERPO --}}
  <div class="modal-body px-4 pb-2 pt-3">

    <h6 class="text-xs text-secondary text-uppercase fw-bold mb-2">Datos del cliente</h6>
    <div class="row g-3 mb-3">
      <div class="col-md-4">
        <label class="text-secondary text-uppercase text-xs d-block mb-1">Nombre</label>
        <input type="text" name="nombre" class="form-control form-control-sm border rounded-2"
          value="{{ $cliente->nombre }}" required>
      </div>

      <div class="col-md-4">
        <label class="text-secondary text-uppercase text-xs d-block mb-1">Teléfono 1</label>
        <input type="text" name="telefono1" class="form-control form-control-sm border rounded-2"
          value="{{ $cliente->telefono1 }}">
      </div>

      <div class="col-md-4">
        <label class="text-secondary text-uppercase text-xs d-block mb-1">Teléfono 2</label>
        <input type="text" name="telefono2" class="form-control form-control-sm border rounded-2"
          value="{{ $cliente->telefono2 }}">
      </div>

      <div class="col-md-4">
        <label class="text-secondary text-uppercase text-xs d-block mb-1">Fecha de contrato</label>
        <input type="date" name="fecha_contrato" class="form-control form-control-sm border rounded-2 bg-light"
          value="{{ $cliente->fecha_contrato->format('Y-m-d') }}" readonly>
      </div>

      <div class="col-md-4">
        <label class="text-secondary text-uppercase text-xs d-block mb-1">Día de cobro</label>
        <input type="number" name="dia_cobro" class="form-control form-control-sm border rounded-2" min="1"
          max="31" value="{{ $cliente->dia_cobro }}" required>
      </div>

      <div class="col-md-4">
        <label class="text-secondary text-uppercase text-xs d-block mb-1">Tipo de cliente</label>
        <select name="tipo" class="form-select form-select-sm border rounded-2" required>
          <option value="A" {{ $cliente->tipo == 'A' ? 'selected' : '' }}>Tipo A</option>
          <option value="B" {{ $cliente->tipo == 'B' ? 'selected' : '' }}>Tipo B</option>
          <option value="C" {{ $cliente->tipo == 'C' ? 'selected' : '' }}>Tipo C</option>
        </select>
      </div>

      <div class="col-md-8">
        <label class="text-secondary text-uppercase text-xs d-block mb-1">Paquete</label>
        <select name="paquete_id" class="form-select form-select-sm border rounded-2" required>
          <option value="">-- Selecciona un paquete --</option>
          @foreach($paquetes as $paquete)
            <option value="{{ $paquete->id }}" {{ $cliente->paquete_id == $paquete->id ? 'selected' : '' }}>
              {{ $paquete->nombre }} - ${{ number_format($paquete->precio, 2) }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="col-md-4">
        <label class="text-secondary text-uppercase text-xs d-block mb-1">Estado</label>
        <select name="activo" class="form-select form-select-sm border rounded-2">
          <option value="1" {{ $cliente->activo ? 'selected' : '' }}>Activo</option>
          <option value="0" {{ !$cliente->activo ? 'selected' : '' }}>Inactivo</option>
        </select>
      </div>
    </div>

    <hr class="my-3">

    <h6 class="text-xs text-secondary text-uppercase fw-bold mb-2">Instalación</h6>
    <div class="row g-3 mb-3">
      <div class="col-md-3">
        <label class="text-secondary text-uppercase text-xs d-block mb-1">Torre</label>
        <input type="text" name="torre" class="form-control form-control-sm border rounded-2"
          value="{{ $cliente->torre }}">
      </div>

      <div class="col-md-3">
        <label class="text-secondary text-uppercase text-xs d-block mb-1">Panel</label>
        <input type="text" name="panel" class="form-control form-control-sm border rounded-2"
          value="{{ $cliente->panel }}">
      </div>

      <div class="col-md-3">
        <label class="text-secondary text-uppercase text-xs d-block mb-1">Dirección MAC</label>
        <input type="text" name="Mac" class="form-control form-control-sm border rounded-2"
          value="{{ $cliente->Mac }}">
      </div>

      <div class="col-md-3">
        <label class="text-secondary text-uppercase text-xs d-block mb-1">Dirección IP</label>
        <input type="text" name="IP" class="form-control form-control-sm border rounded-2"
          value="{{ $cliente->IP }}">
      </div>

      <div class="col-md-4">
        <label class="text-secondary text-uppercase text-xs d-block mb-1">Zona</label>
        <input type="text" name="zona" class="form-control form-control-sm border rounded-2"
          value="{{ $cliente->zona }}">
      </div>

      <div class="col-md-8">
        <label class="text-secondary text-uppercase text-xs d-block mb-1">Dirección</label>
        <input type="text" name="direccion" class="form-control form-control-sm border rounded-2"
          value="{{ $cliente->direccion }}">
      </div>

      <div class="col-md-6">
        <label class="text-secondary text-uppercase text-xs d-block mb-1">Coordenadas</label>
        <input type="text" name="coordenadas" class="form-control form-control-sm border rounded-2"
          value="{{ $cliente->coordenadas }}">
      </div>

      <div class="col-md-6">
        <label class="text-secondary text-uppercase text-xs d-block mb-1">Referencias</label>
        <textarea name="referencias" class="form-control form-control-sm border rounded-2"
          rows="1">{{ $cliente->referencias }}</textarea>
      </div>
    </div>

    <hr class="my-3">

    {{-- Documentos --}}
    <div class="p-3 mb-3 rounded-3" style="background:#f8f9fa;">
      <div class="d-flex align-items-start gap-3">
        <i class="material-icons text-secondary">folder_open</i>
        <div class="flex-grow-1">
          <label class="text-secondary text-uppercase text-xs fw-bold d-block mb-2">Documentos</label>

          <div class="row g-3">
            @include('clientes.partials.campo-documento', ['campo' => 'documento', 'etiqueta' => 'Contrato firmado', 'valor' => $cliente->documento])
            @include('clientes.partials.campo-documento', ['campo' => 'recibo_luz', 'etiqueta' => 'Recibo de luz', 'valor' => $cliente->documento_recibo_luz])
            @include('clientes.partials.campo-documento', ['campo' => 'credencial_elector', 'etiqueta' => 'Credencial del elector', 'valor' => $cliente->documento_credencial_elector])
          </div>
        </div>
      </div>
    </div>

    {{-- Información del equipo --}}
    <details class="mb-1">
      <summary class="text-xs text-secondary text-uppercase fw-bold" style="cursor:pointer;">
        Información del equipo
      </summary>
      <div class="row g-3 mt-1">
        <div class="col-md-4">
          <label class="text-secondary text-uppercase text-xs d-block mb-1">Marca Antena</label>
          <input type="text" name="equipo[marca_antena]" class="form-control form-control-sm border rounded-2"
            value="{{ $cliente->equipos->first()->marca_antena ?? '' }}">
        </div>
        <div class="col-md-4">
          <label class="text-secondary text-uppercase text-xs d-block mb-1">Modelo Antena</label>
          <input type="text" name="equipo[modelo_antena]" class="form-control form-control-sm border rounded-2"
            value="{{ $cliente->equipos->first()->modelo_antena ?? '' }}">
        </div>
        <div class="col-md-4">
          <label class="text-secondary text-uppercase text-xs d-block mb-1">Número de Serie Antena</label>
          <input type="text" name="equipo[numero_serie_antena]" class="form-control form-control-sm border rounded-2"
            value="{{ $cliente->equipos->first()->numero_serie_antena ?? '' }}">
        </div>
        <div class="col-md-4">
          <label class="text-secondary text-uppercase text-xs d-block mb-1">Marca Router</label>
          <input type="text" name="equipo[marca_router]" class="form-control form-control-sm border rounded-2"
            value="{{ $cliente->equipos->first()->marca_router ?? '' }}">
        </div>
        <div class="col-md-4">
          <label class="text-secondary text-uppercase text-xs d-block mb-1">Modelo Router</label>
          <input type="text" name="equipo[modelo_router]" class="form-control form-control-sm border rounded-2"
            value="{{ $cliente->equipos->first()->modelo_router ?? '' }}">
        </div>
        <div class="col-md-4">
          <label class="text-secondary text-uppercase text-xs d-block mb-1">Número de Serie Router</label>
          <input type="text" name="equipo[numero_serie_router]" class="form-control form-control-sm border rounded-2"
            value="{{ $cliente->equipos->first()->numero_serie_router ?? '' }}">
        </div>
      </div>
    </details>
  </div>

  {{-- PIE DE MODAL --}}
  <div class="modal-footer justify-content-between border-0 px-4 pb-4 pt-2">
    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
    @can('Editar clientes')
      <button type="submit" class="btn btn-warning">
        <i class="material-icons align-middle" style="font-size:18px;">save</i> Actualizar
      </button>
    @endcan
  </div>
</form>

<script>
  document.querySelectorAll('#formEditarCliente .btn-cambiar-doc').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const campo = btn.closest('.col-md-4');
      const input = document.getElementById(btn.dataset.target);

      input.classList.remove('d-none');
      campo.querySelector('.doc-existente')?.classList.add('d-none');
      btn.classList.add('d-none');
    });
  });
</script>
