
@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        const opcionesDataTable = {
            pageLength: 10,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-MX.json'
            }
        };

        const mesesAdeudadosUrlBase = "{{ url('/dashboard/clientes') }}";
        const registrarAdeudoUrlBase = "{{ url('/ventas/adeudo') }}";
        const csrfToken = "{{ csrf_token() }}";
        const puedeRegistrarAdeudos = @json(auth()->user()->can('Crear adeudos'));
        let adeudoClienteIdActual = null;

        function formatoMoneda(valor) {
            return '$' + Number(valor).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function recalcularTotalAdeudo(precioPaquete) {
            const total = $('#adeudo_meses_lista .mes-check').length;
            const seleccionados = document.querySelectorAll('#adeudo_meses_lista .mes-check:checked').length;
            document.getElementById('adeudo_total').textContent = formatoMoneda(seleccionados * precioPaquete);
            $('#btnRegistrarAdeudo').prop('disabled', seleccionados === 0);

            const seleccionarTodos = document.getElementById('adeudo_seleccionar_todos');
            if (seleccionarTodos) {
                seleccionarTodos.checked = total > 0 && seleccionados === total;
                seleccionarTodos.indeterminate = seleccionados > 0 && seleccionados < total;
            }
        }

        function toggleTipoPagoFila(checkbox) {
            $(checkbox).closest('.adeudo-mes-item').find('.mes-tipo-pago').prop('disabled', !checkbox.checked);
        }

        function marcarTipoPagoExclusivo(checkboxTipo) {
            const fila = $(checkboxTipo).closest('.adeudo-mes-item');
            if (checkboxTipo.checked) {
                fila.find('.mes-tipo-pago').not(checkboxTipo).prop('checked', false);
            } else {
                // No permitir dejar la fila sin tipo de pago seleccionado.
                checkboxTipo.checked = true;
            }
        }

        $(document).ready(function () {
            const tablaClientesHoy = $('#tabla-clientes-hoy');
            if (tablaClientesHoy.length) {
                tablaClientesHoy.DataTable(opcionesDataTable);
            }

            const tablaClientesAtrasados = $('#tabla-clientes-atrasados');
            if (tablaClientesAtrasados.length) {
                tablaClientesAtrasados.DataTable(opcionesDataTable);
            }

            $(document).on('click', '.btn-ver-adeudo', function () {
                const clienteId = $(this).data('cliente-id');
                const nombre = $(this).data('cliente-nombre');
                adeudoClienteIdActual = clienteId;

                $('#adeudo_cliente_nombre').text(nombre);
                $('#adeudo_total').text(formatoMoneda(0));
                $('#adeudo_meses_lista').empty();
                $('#adeudo_contenido').addClass('d-none');
                $('#adeudo_sin_meses').addClass('d-none');
                $('#btnRegistrarAdeudo').addClass('d-none');
                $('#adeudo_cargando').removeClass('d-none');
                $('#adeudo_seleccionar_todos').prop({ checked: true, indeterminate: false });
                $('#adeudo_meses_lista').off('change');

                fetch(`${mesesAdeudadosUrlBase}/${clienteId}/meses-adeudados`)
                    .then(response => response.json())
                    .then(data => {
                        $('#adeudo_cargando').addClass('d-none');

                        if (!data.meses || data.meses.length === 0) {
                            $('#adeudo_sin_meses').removeClass('d-none');
                            return;
                        }

                        const precioPaquete = Number(data.precio_paquete) || 0;
                        const lista = document.getElementById('adeudo_meses_lista');

                        data.meses.forEach(function (mes) {
                            const id = 'adeudo_mes_' + mes.key;
                            const idEfectivo = id + '_efectivo';
                            const idTransferencia = id + '_transferencia';
                            const item = document.createElement('div');
                            item.className = 'adeudo-mes-item d-flex justify-content-between align-items-center';

                            const tipoPagoHtml = puedeRegistrarAdeudos ? `
                                    <span class="form-check mb-0">
                                        <input class="form-check-input mes-tipo-pago" type="checkbox" value="Efectivo" id="${idEfectivo}" checked>
                                        <label class="form-check-label text-xs" for="${idEfectivo}">Efectivo</label>
                                    </span>
                                    <span class="form-check mb-0">
                                        <input class="form-check-input mes-tipo-pago" type="checkbox" value="Transferencia" id="${idTransferencia}">
                                        <label class="form-check-label text-xs" for="${idTransferencia}">Transferencia</label>
                                    </span>
                                ` : '';

                            item.innerHTML = `
                                <span class="form-check mb-0">
                                    <input class="form-check-input mes-check" type="checkbox" value="${mes.key}" id="${id}" checked ${puedeRegistrarAdeudos ? '' : 'disabled'}>
                                    <label class="form-check-label" for="${id}">${mes.label}</label>
                                </span>
                                <span class="d-flex align-items-center gap-3">
                                    ${tipoPagoHtml}
                                    <span class="text-secondary text-sm">${formatoMoneda(precioPaquete)}</span>
                                </span>
                            `;
                            lista.appendChild(item);
                        });

                        if (puedeRegistrarAdeudos) {
                            $('#adeudo_meses_lista').off('change').on('change', '.mes-check', function () {
                                toggleTipoPagoFila(this);
                                recalcularTotalAdeudo(precioPaquete);
                            });

                            $('#adeudo_meses_lista').on('change', '.mes-tipo-pago', function () {
                                marcarTipoPagoExclusivo(this);
                            });

                            $('#adeudo_seleccionar_todos').off('change').on('change', function () {
                                $('#adeudo_meses_lista .mes-check')
                                    .prop('checked', this.checked)
                                    .each(function () { toggleTipoPagoFila(this); });
                                recalcularTotalAdeudo(precioPaquete);
                            });
                        }

                        recalcularTotalAdeudo(precioPaquete);
                        $('#adeudo_contenido').removeClass('d-none');
                        $('#btnRegistrarAdeudo').removeClass('d-none');
                    })
                    .catch(() => {
                        $('#adeudo_cargando').addClass('d-none');
                        $('#adeudo_sin_meses').text('No se pudo cargar la información del cliente.').removeClass('d-none');
                    });
            });

            $(document).on('click', '#btnRegistrarAdeudo', function () {
                const boton = $(this);
                const mesesSeleccionados = $('#adeudo_meses_lista .adeudo-mes-item')
                    .map(function () {
                        const checkbox = $(this).find('.mes-check');
                        if (!checkbox.is(':checked')) {
                            return null;
                        }
                        return {
                            mes: checkbox.val(),
                            tipo_pago: $(this).find('.mes-tipo-pago:checked').val(),
                        };
                    }).get().filter(Boolean);

                if (mesesSeleccionados.length === 0 || !adeudoClienteIdActual) {
                    return;
                }

                boton.prop('disabled', true).text('Registrando...');

                fetch(`${registrarAdeudoUrlBase}/${adeudoClienteIdActual}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        meses_seleccionados: mesesSeleccionados,
                    }),
                })
                    .then(async response => {
                        const data = await response.json();
                        if (!response.ok) {
                            throw new Error(data.message || 'No se pudo registrar el pago.');
                        }
                        return data;
                    })
                    .then(() => {
                        window.location.reload();
                    })
                    .catch(error => {
                        boton.prop('disabled', false).html('<i class="material-icons me-1" style="font-size:18px; vertical-align:middle;">save</i> Registrar pago');
                        alert(error.message);
                    });
            });
        });
    </script>
@endpush
