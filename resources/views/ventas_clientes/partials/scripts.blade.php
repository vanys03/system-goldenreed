@push('scripts')
<script>
    const ticketBaseUrl = "{{ url('/tickets/reimprimir') }}";
    const ventaDetalleUrl = "{{ url('/api/ventas') }}";
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    let ventaIdActual = null;

    /*
    |--------------------------------------------------------------------------
    | DATOS DESDE LARAVEL
    |--------------------------------------------------------------------------
    */
    const labelsCobrado = @json($labelsCobrado);
    const dataCobrado   = @json($dataCobrado);

    const labelsPagos = @json($labelsPagos);
    const dataPagos   = @json($dataPagos);

    const adelantados = @json($clientesAdelantados);
    const vencidos    = @json($clientesVencidos);

    /*
    |--------------------------------------------------------------------------
    | CONFIGURACIÓN GLOBAL
    |--------------------------------------------------------------------------
    */
    Chart.defaults.responsive = true;
    Chart.defaults.maintainAspectRatio = false;

    /*
    |--------------------------------------------------------------------------
    | GRÁFICA 1 - COBRADO DEL MES
    |--------------------------------------------------------------------------
    */
    const canvasCobrado = document.getElementById('graficoCobrado');

    if (canvasCobrado) {
        new Chart(canvasCobrado, {
            type: 'line',
            data: {
                labels: labelsCobrado,
                datasets: [{
                    label: 'Cobrado',
                    data: dataCobrado,
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.15)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 3,
                    pointBackgroundColor: '#198754'
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        display: true
                    },
                    y: {
                        display: false,
                        beginAtZero: true
                    }
                }
            }
        });
    }

    /*
|--------------------------------------------------------------------------
| GRÁFICA 2 - CLIENTES QUE PAGAN HOY
|--------------------------------------------------------------------------
*/
const canvasCartera = document.getElementById('graficoCartera');

if (canvasCartera) {
    new Chart(canvasCartera, {
        type: 'doughnut',
        data: {
            labels: ['Ya pagaron', 'Pendientes'],
            datasets: [{
                data: [adelantados, vencidos],
                backgroundColor: ['#198754', '#dc3545'],
                borderWidth: 0
            }]
        },
        options: {
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12
                    }
                }
            }
        }
    });
}

    /*
    |--------------------------------------------------------------------------
    | GRÁFICA 3 - PAGOS RECIBIDOS (BARRAS)
    |--------------------------------------------------------------------------
    */
    const canvasPagados = document.getElementById('graficoPagados');

    if (canvasPagados) {
        new Chart(canvasPagados, {
            type: 'bar',
            data: {
                labels: labelsPagos,
                datasets: [{
                    label: 'Pagos',
                    data: dataPagos,
                    backgroundColor: 'rgba(13, 110, 253, 0.7)',
                    borderRadius: 4
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        display: true
                    },
                    y: {
                        display: false,
                        beginAtZero: true
                    }
                }
            }
        });
    }

    /*
|--------------------------------------------------------------------------
| INTERACTIVIDAD DE LA TABLA DE CLIENTES
| + REIMPRESIÓN DE TICKETS
|--------------------------------------------------------------------------
*/
// Delegación de eventos: funciona para filas cargadas inicialmente y las del fetch
document.getElementById('tablaEstadoPagos').addEventListener('click', function (e) {
    const fila = e.target.closest('.fila-cliente');
    if (!fila) return;

    const nombre   = fila.dataset.nombre;
    const total    = fila.dataset.total;
    const dias     = parseInt(fila.dataset.dias);
    const historial = JSON.parse(fila.dataset.historial);

    document.getElementById('detalleNombre').innerText = nombre;
    document.getElementById('detalleTotal').innerText =
        '$' + parseFloat(total).toLocaleString();

    let estadoTexto = '';
    let estadoBadge = '';

    if (historial.length === 0 || isNaN(dias)) {
        estadoTexto = 'SIN PAGOS';
        estadoBadge = 'bg-dark';
    } else if (dias < 0) {
        estadoTexto = 'VENCIDO';
        estadoBadge = 'bg-danger';
    } else if (dias <= 7) {
        estadoTexto = 'PRÓXIMO A VENCER';
        estadoBadge = 'bg-warning text-dark';
    } else {
        estadoTexto = 'AL CORRIENTE';
        estadoBadge = 'bg-success';
    }

    const estado = document.getElementById('detalleEstado');
    estado.innerText = estadoTexto;
    estado.className = 'badge ' + estadoBadge;

    let html = '';

    if (historial.length === 0) {
        html = '<div class="text-muted text-center py-3">Sin historial de pagos</div>';
    } else {
        html = '<div class="timeline-pagos">';

        historial.forEach(pago => {
            const botonVerPago = pago.venta_id ? `
    <button type="button"
            class="btn btn-link p-0 text-primary btn-ver-pago"
            data-id="${pago.venta_id}"
            style="text-decoration:none; font-size:.75rem;">
        Ver venta
    </button>
` : '';

            let colorEstado = 'secondary';
            if (['pagado', 'cobrado'].includes(pago.estado.toLowerCase())) {
                colorEstado = 'success';
            } else if (pago.estado.toLowerCase() === 'pendiente') {
                colorEstado = 'warning';
            } else if (pago.estado.toLowerCase() === 'vencido') {
                colorEstado = 'danger';
            }
            const textoClaro = colorEstado === 'warning' ? 'text-dark' : '';

            html += `
                <div class="d-flex align-items-start pago-item">
                    <span class="dot-estado bg-${colorEstado}"></span>
                    <div class="flex-grow-1 d-flex justify-content-between align-items-start">
                        <div>
                            <small class="fw-bold text-dark d-block">${pago.fecha}</small>
                            <span class="badge bg-${colorEstado} ${textoClaro} mt-1">${pago.estado.toUpperCase()}</span>
                        </div>
                        <div class="text-end">
                            <small class="fw-bold d-block">$${parseFloat(pago.total).toLocaleString()}</small>
                            ${botonVerPago}
                        </div>
                    </div>
                </div>
            `;
        });

        html += '</div>';
    }

    document.getElementById('detalleHistorial').innerHTML = html;

    document.querySelectorAll('.btn-ver-pago').forEach(btn => {
        btn.addEventListener('click', function () {
            ventaIdActual = this.dataset.id;

            fetch(`${ventaDetalleUrl}/${ventaIdActual}`)
                .then(response => {
                    if (!response.ok) throw new Error('Error de red');
                    return response.json();
                })
                .then(data => {
                    document.getElementById('pago_cliente').textContent = data.cliente ?? '—';
                    document.getElementById('pago_paquete').textContent = data.paquete ?? '—';
                    document.getElementById('pago_paquete_precio').textContent =
                        `$${parseFloat(data.paquete_precio ?? 0).toLocaleString()} / mes`;
                    document.getElementById('pago_fecha').textContent =
                        data.fecha_venta ? `${data.fecha_venta}${data.hora_venta ? ' · ' + data.hora_venta : ''}` : '—';
                    document.getElementById('pago_periodo').textContent =
                        (data.periodo_inicio && data.periodo_fin) ? `${data.periodo_inicio} - ${data.periodo_fin}` : '—';
                    document.getElementById('pago_meses').textContent = data.meses ?? '—';
                    document.getElementById('pago_tipo').textContent = data.tipo_pago ?? '—';
                    document.getElementById('pago_subtotal').textContent = `$${parseFloat(data.subtotal ?? 0).toLocaleString()}`;
                    document.getElementById('pago_descuento').textContent = `− $${parseFloat(data.descuento ?? 0).toLocaleString()}`;
                    document.getElementById('pago_recargo_domicilio').textContent = `+ $${parseFloat(data.recargo_domicilio ?? 0).toLocaleString()}`;
                    document.getElementById('pago_recargo_atraso').textContent = `+ $${parseFloat(data.recargo_atraso ?? 0).toLocaleString()}`;
                    document.getElementById('pago_registrado_por').textContent = data.registrado_por ?? '—';
                    document.getElementById('pago_total').textContent = `$${parseFloat(data.total ?? 0).toLocaleString()}`;

                    let colorEstado = 'secondary';
                    const estadoLower = (data.estado ?? '').toLowerCase();
                    if (['pagado', 'cobrado'].includes(estadoLower)) {
                        colorEstado = 'success';
                    } else if (estadoLower === 'pendiente') {
                        colorEstado = 'warning';
                    } else if (estadoLower === 'vencido') {
                        colorEstado = 'danger';
                    }
                    const estadoBadge = document.getElementById('pago_estado');
                    estadoBadge.textContent = (data.estado ?? '—').toUpperCase();
                    estadoBadge.className = `badge bg-${colorEstado} ${colorEstado === 'warning' ? 'text-dark' : ''}`;

                    const modal = new bootstrap.Modal(document.getElementById('modalDetallePago'));
                    modal.show();
                })
                .catch(error => {
                    console.error('Error al cargar el pago:', error);
                    alert('Hubo un problema al cargar los datos del pago.');
                });
        });
    });

    document.querySelectorAll('.fila-cliente').forEach(f => f.classList.remove('table-active'));
    fila.classList.add('table-active');
});

document.getElementById('btnReimprimirTicketPago').addEventListener('click', function () {
    if (!ventaIdActual) return;

    const iframe = document.getElementById('iframeTicket');
    iframe.onload = function () {
        this.contentWindow.focus();
        this.contentWindow.print();
    };
    iframe.src = `${ticketBaseUrl}/${ventaIdActual}`;
});
});
</script>

<script>
$(document).ready(function () {
    $('#tablaPagosAdelantados').DataTable({
        pageLength: 10,
        lengthChange: false,
        ordering: true,
        searching: true,
        info: true,
        responsive: true,
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json'
        },
        order: [[5, 'desc']]
    });
});
</script>
<script>
var dtEstadoPagos;

$(document).ready(function () {
    dtEstadoPagos = $('#tablaEstadoPagos').DataTable({
        pageLength: 10,
        lengthChange: false,
        ordering: true,
        searching: false,
        info: true,
        responsive: true,
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json'
        },
        order: [[5, 'asc']]
    });
});
</script>
@endpush