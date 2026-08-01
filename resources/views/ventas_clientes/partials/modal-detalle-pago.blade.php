<div class="modal fade" id="modalDetallePago" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg rounded-4">

            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center">
                    <div class="icon-shape-sm rounded-circle bg-light d-flex align-items-center justify-content-center me-3">
                        <i class="material-icons text-secondary">receipt_long</i>
                    </div>
                    <div>
                        <p class="text-xs text-secondary text-uppercase mb-0">Detalle del pago</p>
                        <h6 class="fw-bold mb-0" id="pago_cliente">—</h6>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body px-4 pb-4 pt-3">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span id="pago_estado" class="badge">—</span>
                    <small class="text-secondary">Registrado por <strong id="pago_registrado_por" class="text-dark">—</strong></small>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <small class="text-secondary text-uppercase text-xs d-block">Paquete</small>
                        <span class="fw-bold text-dark" id="pago_paquete">—</span>
                    </div>
                    <div class="col-6">
                        <small class="text-secondary text-uppercase text-xs d-block">Precio del paquete</small>
                        <span class="fw-bold text-dark" id="pago_paquete_precio">—</span>
                    </div>
                    <div class="col-6">
                        <small class="text-secondary text-uppercase text-xs d-block">Fecha y hora</small>
                        <span class="fw-bold text-dark" id="pago_fecha">—</span>
                    </div>
                    <div class="col-6">
                        <small class="text-secondary text-uppercase text-xs d-block">Periodo cubierto</small>
                        <span class="fw-bold text-dark" id="pago_periodo">—</span>
                    </div>
                    <div class="col-6">
                        <small class="text-secondary text-uppercase text-xs d-block">Meses pagados</small>
                        <span class="fw-bold text-dark" id="pago_meses">—</span>
                    </div>
                    <div class="col-6">
                        <small class="text-secondary text-uppercase text-xs d-block">Tipo de pago</small>
                        <span class="fw-bold text-dark" id="pago_tipo">—</span>
                    </div>
                </div>

                <hr class="my-3">

                <h6 class="text-xs text-secondary text-uppercase fw-bold mb-2">Desglose</h6>
                <div class="mb-1">
                    <div class="d-flex justify-content-between py-1">
                        <small class="text-secondary">Subtotal (precio × meses)</small>
                        <small class="text-dark" id="pago_subtotal">$0.00</small>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <small class="text-secondary">Descuento</small>
                        <small class="text-danger" id="pago_descuento">− $0.00</small>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <small class="text-secondary">Recargo domicilio</small>
                        <small class="text-dark" id="pago_recargo_domicilio">+ $0.00</small>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <small class="text-secondary">Recargo por atraso</small>
                        <small class="text-dark" id="pago_recargo_atraso">+ $0.00</small>
                    </div>
                </div>

                <hr class="my-3">

                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark">Total pagado</span>
                    <span class="fs-4 fw-bold text-success" id="pago_total">$0.00</span>
                </div>
            </div>

            <div class="modal-footer justify-content-between border-0 px-4 pb-4 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Cerrar
                </button>
                <button type="button" class="btn btn-primary" id="btnReimprimirTicketPago">
                    <i class="material-icons me-1" style="font-size:18px; vertical-align:middle;">print</i> Reimprimir ticket
                </button>
            </div>
        </div>
    </div>
</div>
