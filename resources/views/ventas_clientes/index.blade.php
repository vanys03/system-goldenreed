@push('css')
<link rel="stylesheet"
      href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush
<x-layout bodyClass="g-sidenav-show bg-gray-200">
    <x-navbars.sidebar activePage="ventas_clientes" />
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-navbars.navs.auth titlePage="Estado de Clientes" />

        <div class="container-fluid py-4">
            <div class="row g-4">

                <div class="container-fluid">
                    <div class="row">

                       {{-- ==========================================================
                            TARJETA 1 - COBRADO DEL DÍA
                        ========================================================== --}}
                        <div class="col-md-3 mb-3">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-body d-flex flex-column justify-content-between">
                                    <div>
                                        <h6 class="fw-bold">
                                            <i class="material-icons text-success">attach_money</i>
                                            Cobrado del Día
                                        </h6>

                                        <p class="h5 text-success mb-0" id="totalCobradoDia">
                                            ${{ number_format($totalCobradoDia, 2) }}
                                        </p>
                                    </div>

                                    <div class="mt-3">
                                        <canvas id="graficoCobrado" height="120"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ==========================================================
                        TARJETA 2 - CARTERA TOTAL DE HOY
                        ========================================================== --}}
                        <div class="col-md-3 mb-3">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-body d-flex flex-column justify-content-between">
                                    <div>
                                        <h6 class="fw-bold" id="carteraTotal">
                                            Clientes que pagan hoy: {{ $carteraTotalHoy }}
                                        </h6>

                                        <p class="mt-2 mb-0">
                                            Ya pagaron:
                                            <strong class="text-success" id="clientesAdelantados">
                                                {{ $clientesAdelantados }}
                                            </strong>
                                            <br>
                                            Pendientes:
                                            <strong class="text-danger" id="clientesVencidos">
                                                {{ $clientesVencidos }}
                                            </strong>
                                        </p>
                                    </div>

                                    <div class="mt-3">
                                        <canvas id="graficoCartera" height="120"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ==========================================================
                        TARJETA 3 - PAGOS RECIBIDOS HOY
                        ========================================================== --}}
                        <div class="col-md-3 mb-3">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-body d-flex flex-column justify-content-between">
                                    <div>
                                        <h6 class="fw-bold">Pagos Recibidos - Hoy</h6>
                                        <p class="h4 text-primary mb-0" id="pagosHoyCount">
                                            {{ $pagosHoyCount }}
                                        </p>
                                        <small class="text-muted">pagos registrados hoy</small>
                                    </div>

                                    <div class="mt-3">
                                        <canvas id="graficoPagados" height="120"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ==========================================================
                        TARJETA 4 - CLIENTES POR TIPO
                        ========================================================== --}}
                        <div class="col-md-3 mb-3">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-body">
                                    <div>
                                        <h6 class="fw-bold">
                                            <i class="material-icons text-primary">groups</i>
                                            Clientes por Tipo
                                        </h6>

                                        <div class="mt-3" id="clientesPorTipoContainer">
                                            @foreach ($clientesPorTipo as $tipo => $total)
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="fw-semibold">Tipo {{ $tipo }}</span>
                                                    <span class="badge bg-primary rounded-pill">
                                                        {{ $total }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="container-fluid py-4">
                    <div class="card shadow-sm border-0">

                        <div class="card-header bg-white border-0 pb-0">
                            <h6 class="fw-bold mb-3">CLIENTES CON PAGO ADELANTADO</h6>
                        </div>

                        <div class="table-responsive px-3 pb-3">
                            <table id="tablaPagosAdelantados" class="table align-items-center mb-0">

                                <thead class="text-uppercase text-secondary text-xs">
                                    <tr>
                                        <th>Nombre Cliente</th>
                                        <th>Último Pago</th>
                                        <th>Meses Pagados</th>
                                        <th>Rango Cubierto</th>
                                        <th>Próximo Vencimiento</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>

                                <tbody class="text-sm">

                                    @forelse($clientesPagoAdelantado as $cliente)
                                        <tr>
                                            <td class="fw-bold text-dark">
                                                {{ $cliente->nombre }}
                                            </td>

                                            <td>
                                                {{ $cliente->ultimo_pago }}
                                            </td>

                                            <td>
                                                <span class="badge bg-warning text-dark px-3 py-2">
                                                    {{ $cliente->meses_pagados }}
                                                    {{ $cliente->meses_pagados == 1 ? 'mes' : 'meses' }}
                                                </span>
                                            </td>

                                            <td>
                                                <span class="badge bg-info text-dark px-3 py-2">
                                                    {{ $cliente->rango_cubierto }}
                                                </span>
                                            </td>

                                            <td>
                                                <span class="badge rounded-pill bg-success px-3 py-2">
                                                    {{ $cliente->dias_restantes }} días
                                                </span>
                                            </td>

                                            <td>
                                                <span class="badge bg-success px-3 py-2">
                                                    {{ $cliente->estado }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                No hay clientes con pagos adelantados.
                                            </td>
                                        </tr>
                                    @endforelse

                                </tbody>

                            </table>
                        </div>

                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-lg-8 d-flex">
                        <div class="card w-100 shadow-sm border-0">

                            <div class="card-header bg-white border-0 pb-0">
                                <h6 class="mb-3 fw-bold">TABLA GENERAL DE ESTADO DE PAGOS</h6>

                                <div class="row g-2 align-items-center mb-2">

                                    <div class="col-md-3">
                                        <input
                                            type="text"
                                            id="filtroBuscar"
                                            class="form-control form-control-sm"
                                            placeholder="Buscar cliente">
                                    </div>

                                    <div class="col-md-2">
                                        <select id="filtroDias" class="form-select form-select-sm">
                                            <option value="">Días restantes</option>
                                            <option value="menos_7">Menos de 7 días</option>
                                            <option value="vencidos">Vencidos</option>
                                            <option value="adelantados">Adelantados</option>
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <select id="filtroDiaPago" class="form-select form-select-sm">
                                            <option value="">Día de pago</option>
                                            @for ($i = 1; $i <= 31; $i++)
                                                <option value="{{ $i }}">Día {{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <button id="btnLimpiarFiltros" class="btn btn-outline-secondary btn-sm w-100">
                                            LIMPIAR
                                        </button>
                                    </div>

                                    <div class="col-md-1 d-flex align-items-center">
                                        <span id="filtroSpinner" class="spinner-border spinner-border-sm text-secondary d-none" role="status"></span>
                                    </div>

                                </div>

                                <script>
                                    document.addEventListener('DOMContentLoaded', function () {
                                        const urlFiltrar = "{{ route('ventas_clientes.filtrar') }}";
                                        const buscar     = document.getElementById('filtroBuscar');
                                        const dias       = document.getElementById('filtroDias');
                                        const diaPago    = document.getElementById('filtroDiaPago');
                                        const limpiar    = document.getElementById('btnLimpiarFiltros');
                                        const spinner    = document.getElementById('filtroSpinner');

                                        function filaHTML(c) {
                                            const wc = c.badge === 'warning' ? 'text-dark' : '';
                                            const historial = JSON.stringify(c.historial).replace(/'/g, '&#39;');
                                            const activoBadge = c.activo
                                                ? '<span class="badge bg-success">Activo</span>'
                                                : '<span class="badge bg-secondary">Inactivo</span>';
                                            return `<tr class="fila-cliente" style="cursor:pointer"
                                                        data-nombre="${c.nombre}"
                                                        data-total="${c.total_pagado}"
                                                        data-dias="${c.dias}"
                                                        data-dia-cobro="${c.dia_cobro}"
                                                        data-historial='${historial}'>
                                                <td class="fw-bold">${c.nombre}</td>
                                                <td>${c.dia_cobro}</td>
                                                <td>${c.ultimo_pago}</td>
                                                <td><span class="badge rounded-pill bg-${c.badge} ${wc} px-3">${c.texto_dias}</span></td>
                                                <td><span class="badge bg-${c.badge} ${wc}">${c.estado}</span></td>
                                                <td>${activoBadge}</td>
                                            </tr>`;
                                        }

                                        function aplicar() {
                                            const params = new URLSearchParams();
                                            if (buscar.value.trim()) params.set('buscar', buscar.value.trim());
                                            if (dias.value)          params.set('dias_restantes', dias.value);
                                            if (diaPago.value)       params.set('dia_pago', diaPago.value);

                                            spinner.classList.remove('d-none');

                                            fetch(`${urlFiltrar}?${params.toString()}`)
                                                .then(r => r.json())
                                                .then(clientes => {
                                                    const dt = window.dtEstadoPagos;
                                                    if (!dt) return;

                                                    dt.clear();

                                                    clientes.forEach(c => {
                                                        // Crear el TR como nodo DOM para preservar los data-*
                                                        const temp = document.createElement('tbody');
                                                        temp.innerHTML = filaHTML(c);
                                                        dt.row.add(temp.firstElementChild);
                                                    });

                                                    dt.draw();
                                                })
                                                .finally(() => spinner.classList.add('d-none'));
                                        }

                                        let debounce;
                                        buscar.addEventListener('input', function () {
                                            clearTimeout(debounce);
                                            debounce = setTimeout(aplicar, 350);
                                        });
                                        dias.addEventListener('change', aplicar);
                                        diaPago.addEventListener('change', aplicar);

                                        limpiar.addEventListener('click', function () {
                                            buscar.value  = '';
                                            dias.value    = '';
                                            diaPago.value = '';
                                            aplicar();
                                        });
                                    });
                                </script>
                            </div>

                            <div class="table-responsive px-3 pb-3">
                                <table id="tablaEstadoPagos" class="table align-items-center mb-0">

                                    <thead class="text-uppercase text-secondary text-xs">
                                        <tr>
                                            <th>Nombre Cliente</th>
                                            <th>Día de Cobro</th>
                                            <th>Último Pago</th>
                                            <th>Próximo Vencimiento</th>
                                            <th>Estado</th>
                                            <th>Cliente</th>
                                        </tr>
                                    </thead>

                                    <tbody class="text-sm">

                                        @forelse($clientes as $cliente)
                                            <tr
                                                class="fila-cliente"
                                                style="cursor:pointer"
                                                data-nombre="{{ $cliente->nombre }}"
                                                data-total="{{ $cliente->total_pagado }}"
                                                data-dias="{{ $cliente->dias }}"
                                                data-dia-cobro="{{ $cliente->dia_cobro }}"
                                                data-historial='@json($cliente->historial)'>

                                                <td class="fw-bold">
                                                    {{ $cliente->nombre }}
                                                </td>
                                                <td>
                                                    {{ $cliente->dia_cobro }}
                                                </td>

                                                <td>
                                                    {{ $cliente->ultimo_pago }}
                                                </td>

                                                <td>
                                                    <span class="badge rounded-pill bg-{{ $cliente->badge }} {{ $cliente->badge === 'warning' ? 'text-dark' : '' }} px-3">
                                                        {{ $cliente->texto_dias }}
                                                    </span>
                                                </td>

                                                <td>
                                                    <span class="badge bg-{{ $cliente->badge }} {{ $cliente->badge === 'warning' ? 'text-dark' : '' }}">
                                                        {{ $cliente->estado }}
                                                    </span>
                                                </td>

                                                <td>
                                                    @if($cliente->activo)
                                                        <span class="badge bg-success">Activo</span>
                                                    @else
                                                        <span class="badge bg-secondary">Inactivo</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td
                                                    colspan="6"
                                                    class="text-center text-muted py-4">
                                                    No se encontraron clientes.
                                                </td>
                                            </tr>
                                        @endforelse

                                    </tbody>

                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 mb-4">
                        <div class="card shadow-sm border-0 p-3 h-100">
                            <h6 class="fw-bold mb-3">DETALLE DE PAGO:
                                <span id="detalleNombre" class="text-uppercase text-primary">Selecciona un cliente</span>
                            </h6>
                            <div class="d-flex justify-content-between text-center mb-3">
                                <div>
                                    <small class="text-muted d-block">Total Pagado</small>
                                    <strong id="detalleTotal">$0.00</strong>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Estado Actual</small>
                                    <strong id="detalleEstado" class="text-secondary">SIN SELECCIONAR</strong>
                                </div>
                            </div>
                            <hr>
                            <h6 class="text-sm fw-bold mb-3">HISTORIAL DE PAGOS</h6>
                            <div id="detalleHistorial">
                                <div class="text-center text-muted py-4">
                                    <i class="material-icons opacity-6 mb-2" style="font-size:40px;">payments</i>
                                    <p class="mb-0">Selecciona un cliente para ver detalles</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                

                
                @include('ventas_clientes.partials.scripts')
            </div>
    </main>
    <iframe id="iframeTicket" style="display:none;"></iframe>
</x-layout>