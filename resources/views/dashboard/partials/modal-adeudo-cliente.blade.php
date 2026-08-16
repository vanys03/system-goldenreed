<div class="modal fade" id="modalAdeudoCliente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">

            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center">
                    <div class="icon-shape-sm rounded-circle bg-light d-flex align-items-center justify-content-center me-3">
                        <i class="material-icons text-secondary">receipt_long</i>
                    </div>
                    <div>
                        <p class="text-xs text-secondary text-uppercase mb-0">Meses adeudados</p>
                        <h6 class="fw-bold mb-0" id="adeudo_cliente_nombre">—</h6>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body px-4 pb-4 pt-3">

                <div id="adeudo_cargando" class="text-center text-secondary py-3 d-none">
                    Cargando meses adeudados...
                </div>

                <div id="adeudo_sin_meses" class="text-center text-secondary py-3 d-none">
                    Este cliente no tiene meses pendientes.
                </div>

                <div id="adeudo_contenido" class="d-none">
                    @can('Crear adeudos')
                        <div class="alert alert-warning py-2 px-3 text-xs mb-3" role="alert">
                            <i class="material-icons align-middle" style="font-size:16px;">info</i>
                            Este pago se registrará como capturado desde el módulo de adeudos, para meses que no se
                            habían ingresado a tiempo en el sistema.
                        </div>
                    @else
                        <div class="alert alert-light border py-2 px-3 text-xs mb-3" role="alert">
                            <i class="material-icons align-middle" style="font-size:16px;">visibility</i>
                            No tienes permiso para registrar pagos desde aquí. Solo puedes consultar los meses
                            pendientes de este cliente.
                        </div>
                    @endcan

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <p class="text-xs text-secondary text-uppercase fw-bold mb-0">
                            @can('Crear adeudos')
                                Selecciona los meses a cubrir y el tipo de pago de cada uno
                            @else
                                Meses pendientes de pago
                            @endcan
                        </p>
                        @can('Crear adeudos')
                            <span class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" id="adeudo_seleccionar_todos" checked>
                                <label class="form-check-label text-xs" for="adeudo_seleccionar_todos">Seleccionar todos</label>
                            </span>
                        @endcan
                    </div>
                    <div id="adeudo_meses_lista" class="d-flex flex-column gap-2 mb-3">
                    </div>

                    <hr class="my-3">

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-dark">Total a pagar</span>
                        <span class="fs-4 fw-bold text-success" id="adeudo_total">$0.00</span>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 px-4 pb-4 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Cerrar
                </button>
                @can('Crear adeudos')
                    <button type="button" class="btn btn-success d-none" id="btnRegistrarAdeudo">
                        <i class="material-icons me-1" style="font-size:18px; vertical-align:middle;">save</i>
                        Registrar pago
                    </button>
                @endcan
            </div>
        </div>
    </div>
</div>
