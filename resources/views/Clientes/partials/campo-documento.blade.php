@php $inputId = 'doc-' . $campo; @endphp
<div class="col-md-4">
  <label class="text-secondary text-uppercase text-xs d-block mb-1">{{ $etiqueta }}</label>

  @if($valor)
    <div class="doc-existente d-flex align-items-center justify-content-between px-2 py-1 rounded-2 mb-1 text-xs fw-bold">
      <span class="d-flex align-items-center">
        <i class="material-icons me-1" style="font-size:16px;">check_circle</i> Documento cargado
      </span>
      <a href="{{ route('ver.documento', [$cliente->id, basename($valor)]) }}" target="_blank" class="text-success">Ver</a>
    </div>
    <button type="button" class="btn btn-outline-secondary btn-sm w-100 btn-cambiar-doc" data-target="{{ $inputId }}">
      Cargar uno nuevo
    </button>
    <input type="file" id="{{ $inputId }}" name="{{ $campo }}"
      class="form-control form-control-sm border rounded-2 mt-2 d-none" accept="image/*,.pdf">
  @else
    <input type="file" id="{{ $inputId }}" name="{{ $campo }}"
      class="form-control form-control-sm border rounded-2" accept="image/*,.pdf">
  @endif
</div>
