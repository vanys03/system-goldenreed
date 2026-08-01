@push('scripts')
<script>
    const ticketBaseUrl = "{{ url('/tickets/reimprimir') }}";
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

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
    let estadoClase = '';

    if (dias < 0) {
        estadoTexto = 'VENCIDO';
        estadoClase = 'text-danger';
    } else if (dias <= 7) {
        estadoTexto = 'PRÓXIMO A VENCER';
        estadoClase = 'text-warning';
    } else {
        estadoTexto = 'AL CORRIENTE';
        estadoClase = 'text-success';
    }

    const estado = document.getElementById('detalleEstado');
    estado.innerText = estadoTexto;
    estado.className = estadoClase;

    let html = '';

    if (historial.length === 0) {
        html = '<div class="text-muted">Sin historial de pagos</div>';
    } else {
        historial.forEach(pago => {
            const botonTicket = pago.venta_id ? `
    <button type="button"
            class="btn btn-link p-0 ms-2 text-primary btn-reimprimir-ticket"
            data-id="${pago.venta_id}"
            title="Reimprimir ticket"
            style="text-decoration:none;">
        <i class="material-icons" style="font-size:18px; vertical-align:middle;">receipt_long</i>
    </button>
` : '';

            let colorEstado = 'text-secondary';
            if (['pagado', 'cobrado'].includes(pago.estado.toLowerCase())) {
                colorEstado = 'text-success';
            } else if (pago.estado.toLowerCase() === 'pendiente') {
                colorEstado = 'text-warning';
            } else if (pago.estado.toLowerCase() === 'vencido') {
                colorEstado = 'text-danger';
            }

            html += `
                <div class="d-flex align-items-start mb-3">
                    <span class="bg-success rounded-circle me-2 mt-1" style="width:8px;height:8px;"></span>
                    <div class="flex-grow-1">
                        <small class="fw-bold text-secondary">Pago mensualidad (${pago.fecha})</small>
                        ${botonTicket}<br>
                        <small>
                            $${parseFloat(pago.total).toLocaleString()}
                            - <span class="${colorEstado}">${pago.estado.toUpperCase()}</span>
                        </small>
                    </div>
                </div>
            `;
        });
    }

    document.getElementById('detalleHistorial').innerHTML = html;

    document.querySelectorAll('.btn-reimprimir-ticket').forEach(btn => {
        btn.addEventListener('click', function () {
            const ventaId = this.dataset.id;
            const iframe = document.getElementById('iframeTicket');
            iframe.onload = function () {
                this.contentWindow.focus();
                this.contentWindow.print();
            };
            iframe.src = `${ticketBaseUrl}/${ventaId}`;
        });
    });

    document.querySelectorAll('.fila-cliente').forEach(f => f.classList.remove('table-active'));
    fila.classList.add('table-active');
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
        order: [[4, 'asc']]
    });
});
</script>
@endpush