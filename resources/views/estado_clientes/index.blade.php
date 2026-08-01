<x-layout bodyClass="g-sidenav-show bg-gray-200">
    <x-navbars.sidebar activePage='estado_clientes' />
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-navbars.navs.auth titlePage="Estado de Clientes" />

        <div class="container-fluid py-4">

            <!-- Métricas rápidas en dos tablas -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header text-center">
                            <h6 class="fw-bold">Métricas Generales</h6>
                        </div>
                        <div class="card-body p-0">
                            <table class="table mb-0 text-center">
                                <thead class="table-light">
                                    <tr>
                                        <th>Total</th>
                                        <th>Activos</th>
                                        <th>Inactivos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>{{ $total ?? 0 }}</td>
                                        <td class="text-success fw-bold">{{ $activos ?? 0 }}</td>
                                        <td class="text-danger fw-bold">{{ $inactivos ?? 0 }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header text-center">
                            <h6 class="fw-bold">Métricas por Tipo</h6>
                        </div>
                        <div class="card-body p-0">
                            <table class="table mb-0 text-center">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tipo A</th>
                                        <th>Tipo B</th>
                                        <th>Tipo C</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-primary fw-bold">{{ $tipoA ?? 0 }}</td>
                                        <td class="text-info fw-bold">{{ $tipoB ?? 0 }}</td>
                                        <td class="text-warning fw-bold">{{ $tipoC ?? 0 }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FILTROS -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="fw-bold mb-0">Filtros</h5>
                </div>

                <div class="card-body">

                    <form id="form-filtros" class="row g-3" method="GET" action="{{ route('reporte_clientes.index') }}"
                        autocomplete="off">

                        <!-- Fecha Inicio -->
                        <div class="col-md-3">
                            <label class="form-label fw-bold">
                                Fecha Inicio
                            </label>

                            <input type="date" name="fecha_inicio" class="form-control"
                                value="{{ request('fecha_inicio') }}">
                        </div>

                        <!-- Fecha Fin -->
                        <div class="col-md-3">
                            <label class="form-label fw-bold">
                                Fecha Fin
                            </label>

                            <input type="date" name="fecha_fin" class="form-control" value="{{ request('fecha_fin') }}">
                        </div>

                        <!-- Estado -->
                        <div class="col-md-3">
                            <label class="form-label fw-bold">
                                Estado
                            </label>

                            <select name="estado" class="form-select">

                                <option value="Todos" {{ request('estado') == 'Todos' ? 'selected' : '' }}>
                                    Todos
                                </option>

                                <option value="Activos" {{ request('estado') == 'Activos' ? 'selected' : '' }}>
                                    Activos
                                </option>

                                <option value="Inactivos" {{ request('estado') == 'Inactivos' ? 'selected' : '' }}>
                                    Inactivos
                                </option>

                            </select>
                        </div>

                        <!-- Tipo -->
                        <div class="col-md-3">
                            <label class="form-label fw-bold">
                                Tipo de Cliente
                            </label>

                            <select name="tipo" class="form-select">

                                <option value="Todos" {{ request('tipo') == 'Todos' ? 'selected' : '' }}>
                                    Todos
                                </option>

                                <option value="A" {{ request('tipo') == 'A' ? 'selected' : '' }}>
                                    Tipo A
                                </option>

                                <option value="B" {{ request('tipo') == 'B' ? 'selected' : '' }}>
                                    Tipo B
                                </option>

                                <option value="C" {{ request('tipo') == 'C' ? 'selected' : '' }}>
                                    Tipo C
                                </option>

                            </select>
                        </div>

                        <!-- Botones -->
                        <div class="col-12 d-flex justify-content-end gap-2 mt-3">

                            <a href="{{ route('reporte_clientes.index') }}" class="btn btn-outline-secondary">
                                Limpiar
                            </a>

                            <button type="submit" class="btn btn-primary">
                                Generar Reporte
                            </button>

                        </div>
                    </form>
                </div>
            </div>


            <!-- GRAFICAS -->
            <div class="card shadow-sm mb-4">

                <div class="card-header">
                    <h5 class="fw-bold mb-0">
                        Resumen Visual
                    </h5>
                </div>

                <div class="card-body">

                    <div class="row">

                        <!-- Grafica General -->
                        <div class="col-md-6 mb-4">

                            <div class="border rounded p-3 h-100">

                                <h6 class="text-center fw-bold mb-3">
                                    Estado General de Clientes
                                </h6>

                                <canvas id="graficoCircular" style="max-height: 320px;">
                                </canvas>

                            </div>
                        </div>

                        <!-- Grafica Tipos -->
                        <div class="col-md-6 mb-4">

                            <div class="border rounded p-3 h-100">

                                <h6 class="text-center fw-bold mb-3">
                                    Clientes Inactivos por Tipo
                                </h6>

                                <canvas id="graficoLineas" style="max-height: 320px;">
                                </canvas>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <!-- RESUMEN NUMERICO -->
            <div class="row mb-4">

                <!-- Clientes Nuevos -->
                <div class="col-md-4 mb-3">

                    <div class="card shadow-sm border-start border-success border-4">

                        <div class="card-body text-center">

                            <h6 class="text-muted mb-2">
                                Clientes Nuevos
                            </h6>

                            <h2 class="fw-bold text-success">
                                {{ $clientesNuevos ?? 0 }}
                            </h2>

                            <small class="text-muted">
                                Altas registradas en el período
                            </small>

                        </div>
                    </div>
                </div>

                <!-- Clientes Inactivos -->
                <div class="col-md-4 mb-3">

                    <div class="card shadow-sm border-start border-danger border-4">

                        <div class="card-body text-center">

                            <h6 class="text-muted mb-2">
                                Clientes Inactivos
                            </h6>

                            <h2 class="fw-bold text-danger">
                                {{ $clientesInactivos ?? 0 }}
                            </h2>

                            <small class="text-muted">
                                Clientes dados de baja
                            </small>

                        </div>
                    </div>
                </div>

                <!-- Clientes Activos -->
                <div class="col-md-4 mb-3">

                    <div class="card shadow-sm border-start border-primary border-4">

                        <div class="card-body text-center">

                            <h6 class="text-muted mb-2">
                                Clientes Activos
                            </h6>

                            <h2 class="fw-bold text-primary">
                                {{ $activos ?? 0 }}
                            </h2>

                            <small class="text-muted">
                                Clientes actualmente activos
                            </small>

                        </div>
                    </div>
                </div>

            </div>


            <!-- RESUMEN POR TIPO -->
            <div class="row mb-4">

                <div class="col-md-4 mb-3">

                    <div class="card shadow-sm">

                        <div class="card-body text-center">

                            <h6 class="fw-bold text-primary">
                                Tipo A Inactivos
                            </h6>

                            <h3>
                                {{ $tipoAInactivos ?? 0 }}
                            </h3>

                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">

                    <div class="card shadow-sm">

                        <div class="card-body text-center">

                            <h6 class="fw-bold text-info">
                                Tipo B Inactivos
                            </h6>

                            <h3>
                                {{ $tipoBInactivos ?? 0 }}
                            </h3>

                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">

                    <div class="card shadow-sm">

                        <div class="card-body text-center">

                            <h6 class="fw-bold text-warning">
                                Tipo C Inactivos
                            </h6>

                            <h3>
                                {{ $tipoCInactivos ?? 0 }}
                            </h3>

                        </div>
                    </div>
                </div>

            </div>
            <!-- Tabla de Clientes -->
            <div class="card shadow-sm">
                <div class="table-responsive">
                    <div class="card-body">
                        <div id="loader-clientes" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status"></div>
                        </div>

                        <table id="tabla-clientes" class="table align-items-center mb-0 w-100 d-none">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Fecha de alta</th>
                                    <th>Zona</th>
                                    <th>Fecha de desactivación</th>
                                    <th>Estado</th>
                                    <th>Tipo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($clientes as $cliente)
                                    <tr>
                                        <td>{{ $cliente->nombre }}</td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($cliente->fecha_contrato)->translatedFormat('d/m/Y') }}
                                        </td>
                                        <td>{{ $cliente->zona }}</td>
                                        <td>
                                            @if(!$cliente->activo && $cliente->updated_at)
                                                {{ \Carbon\Carbon::parse($cliente->updated_at)->translatedFormat('d/m/Y H:i') }}
                                            @else
                                                --
                                            @endif
                                        </td>
                                        <td>
                                            <button
                                                class="btn btn-sm {{ $cliente->activo ? 'btn-success' : 'btn-secondary' }} toggle-estado"
                                                data-id="{{ $cliente->id }}">
                                                {{ $cliente->activo ? 'Activo' : 'Inactivo' }}
                                            </button>
                                        </td>
                                        <td>
                                            <select class="form-select form-select-sm change-tipo"
                                                data-id="{{ $cliente->id }}">
                                                <option value="A" {{ $cliente->tipo === 'A' ? 'selected' : '' }}>A</option>
                                                <option value="B" {{ $cliente->tipo === 'B' ? 'selected' : '' }}>B</option>
                                                <option value="C" {{ $cliente->tipo === 'C' ? 'selected' : '' }}>C</option>
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @include('estado_clientes.partials.scripts')
            @include('estado_clientes.partials.script')
        </div>
    </main>
</x-layout>