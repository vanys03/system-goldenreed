@push('scripts')
    <!-- CHART JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>

        /*
        |--------------------------------------------------------------------------
        | VARIABLES
        |--------------------------------------------------------------------------
        */

        const clientesNuevos = {{ $clientesNuevos ?? 0 }};
        const clientesInactivos = {{ $clientesInactivos ?? 0 }};
        const activos = {{ $activos ?? 0 }};

        const tipoAInactivos = {{ $tipoAInactivos ?? 0 }};
        const tipoBInactivos = {{ $tipoBInactivos ?? 0 }};
        const tipoCInactivos = {{ $tipoCInactivos ?? 0 }};


        /*
        |--------------------------------------------------------------------------
        | GRAFICA GENERAL
        |--------------------------------------------------------------------------
        */

        const ctxCircular = document
            .getElementById('graficoCircular')
            .getContext('2d');

        new Chart(ctxCircular, {

            type: 'doughnut',

            data: {

                labels: [
                    'Clientes Nuevos',
                    'Clientes Inactivos',
                    'Clientes Activos'
                ],

                datasets: [{
                    data: [
                        clientesNuevos,
                        clientesInactivos,
                        activos
                    ],

                    backgroundColor: [
                        '#198754',
                        '#dc3545',
                        '#0d6efd'
                    ],

                    borderWidth: 1
                }]
            },

            options: {

                responsive: true,

                plugins: {

                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });


        /*
        |--------------------------------------------------------------------------
        | GRAFICA TIPOS INACTIVOS
        |--------------------------------------------------------------------------
        */

        const ctxLineas = document
            .getElementById('graficoLineas')
            .getContext('2d');

        new Chart(ctxLineas, {

            type: 'bar',

            data: {

                labels: [
                    'Tipo A',
                    'Tipo B',
                    'Tipo C'
                ],

                datasets: [{

                    label: 'Clientes Inactivos',

                    data: [
                        tipoAInactivos,
                        tipoBInactivos,
                        tipoCInactivos
                    ],

                    backgroundColor: [
                        '#0d6efd',
                        '#17a2b8',
                        '#ffc107'
                    ],

                    borderRadius: 6
                }]
            },

            options: {

                responsive: true,

                plugins: {

                    legend: {
                        display: false
                    }
                },

                scales: {

                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });

    </script>
@endpush