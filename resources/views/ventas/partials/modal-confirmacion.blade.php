@push('styles')
<style>
.icon-shape-sm{
    width: 40px;
    height: 40px;
    min-width: 40px;
}
</style>
@endpush

<!-- Modal Confirmación de Venta -->
<div class="modal fade" id="modalConfirmarVenta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">

            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center">
                    <div class="icon-shape-sm rounded-circle bg-light d-flex align-items-center justify-content-center me-3">
                        <i class="material-icons text-secondary">receipt_long</i>
                    </div>
                    <div>
                        <p class="text-xs text-secondary text-uppercase mb-0">Confirmar venta</p>
                        <h6 class="fw-bold mb-0" id="resumen_cliente">—</h6>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body px-4 pb-4 pt-3">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span id="resumen_tipo_pago" class="badge bg-secondary">—</span>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <small class="text-secondary text-uppercase text-xs d-block">Paquete</small>
                        <span class="fw-bold text-dark" id="resumen_paquete">—</span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-secondary text-uppercase text-xs d-block">Meses</small>
                        <span class="fw-bold text-dark" id="resumen_meses">—</span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-secondary text-uppercase text-xs d-block">Cobrado por</small>
                        <span class="fw-bold text-dark">{{ Auth::user()->name }}</span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-secondary text-uppercase text-xs d-block">Día de pago</small>
                        <span class="fw-bold text-dark" id="resumen_dia_pago">—</span>
                    </div>
                    <div class="col-md-8">
                        <small class="text-secondary text-uppercase text-xs d-block">Mes que está pagando</small>
                        <span class="fw-bold text-dark">{{ ucfirst(now()->locale('es')->isoFormat('MMMM [de] YYYY')) }}</span>
                    </div>
                </div>

                <hr class="my-3">

                <h6 class="text-xs text-secondary text-uppercase fw-bold mb-2">Desglose</h6>
                <div class="row g-3 mb-1">
                    <div class="col-md-4">
                        <small class="text-secondary text-uppercase text-xs d-block">Descuento</small>
                        <small class="text-danger fw-bold">− $<span id="resumen_descuento">0.00</span></small>
                    </div>
                    <div class="col-md-4">
                        <small class="text-secondary text-uppercase text-xs d-block">Recargo domicilio</small>
                        <small class="text-dark fw-bold">+ $<span id="resumen_recargo_domicilio">0.00</span></small>
                    </div>
                    <div class="col-md-4">
                        <small class="text-secondary text-uppercase text-xs d-block">Recargo por atraso</small>
                        <small class="text-dark fw-bold">+ $<span id="resumen_recargo_falta_pago">0.00</span></small>
                    </div>
                </div>

                <hr class="my-3">

                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark">Total</span>
                    <span class="fs-4 fw-bold text-success">$<span id="resumen_total">0.00</span></span>
                </div>

                <div class="mt-3">
                    <hr class="my-3">
                    <div class="row g-3 align-items-end">
                        <div class="col-6">
                            <label for="modal_monto_recibido" class="text-secondary text-uppercase text-xs d-block mb-1">
                                Efectivo recibido
                            </label>
                            <input type="number" id="modal_monto_recibido" class="form-control form-control-sm border border-secondary-subtle rounded-2"
                                min="0" step="0.01" placeholder="0.00">
                        </div>
                        <div class="col-6 text-end">
                            <small class="text-secondary text-uppercase text-xs d-block">Cambio</small>
                            <span class="fs-5 fw-bold text-success">$<span id="resumen_cambio">0.00</span></span>
                        </div>
                    </div>
                    <small id="avisoEfectivoInsuficiente" class="text-danger d-none d-block mt-2">
                        El efectivo recibido debe ser mayor o igual al total.
                    </small>
                </div>
            </div>

            <div class="modal-footer justify-content-between border-0 px-4 pb-4 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" id="btnEnviarFormulario" class="btn btn-success">
                    <i class="material-icons me-1" style="font-size:18px; vertical-align:middle;">check_circle</i> Confirmar
                </button>
            </div>
        </div>
    </div>
</div>
