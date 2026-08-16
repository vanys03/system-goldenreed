@push('scripts')
@if(session('contrato_url'))
<script>
    window.open('{{ session('contrato_url') }}', '_blank');
</script>
@endif
<script>
    const tablaClientes = $('#tabla-clientes');
    if (tablaClientes.length) {
        tablaClientes.DataTable({
            processing: false,
            serverSide: true,
            ajax: '{{ route('clientes.data') }}',
            pageLength: 10,
            deferRender: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-MX.json'
            },
            columns: [
                { data: 'nombre', name: 'nombre' },
                { data: 'telefono1', name: 'telefono1' },
                { data: 'telefono2', name: 'telefono2' },
                { data: 'dia_cobro', name: 'dia_cobro' },
                { data: 'referencias', name: 'referencias' },
                { data: 'tipo', name: 'tipo', orderable: false },
                { data: 'acciones', name: 'acciones', orderable: false, searchable: false },
                { data: 'estado', name: 'estado', searchable: false }
            ],
            order: [],
            initComplete: function () {
                $('#loader-clientes').remove();
                tablaClientes.removeClass('d-none');
            }
        });
    }

    document.getElementById('btn-confirmar-contrato-blanco')?.addEventListener('click', function () {
        document.getElementById('form-contrato-blanco').submit();
        setTimeout(refrescarFoliosDisponibles, 1500);
    });

    function actualizarEstadoFolio() {
        const checkbox = document.getElementById('check-sin-folio');
        const select = document.getElementById('select-folio-cliente');
        const aviso = document.getElementById('sin-folios-disponibles');
        const btnGuardar = document.getElementById('btn-guardar-cliente');

        if (!select) {
            return;
        }

        const sinFolio = checkbox?.checked ?? false;
        const hayFolios = select.options.length > 1;

        select.disabled = sinFolio || !hayFolios;
        select.required = !sinFolio;

        if (aviso) {
            aviso.classList.toggle('d-none', sinFolio || hayFolios);
        }
        if (btnGuardar) {
            btnGuardar.disabled = !sinFolio && !hayFolios;
        }
    }

    function refrescarFoliosDisponibles() {
        const select = document.getElementById('select-folio-cliente');

        if (!select) {
            return;
        }

        fetch('{{ route('clientes.folios-disponibles') }}')
            .then(response => response.json())
            .then(folios => {
                const seleccionActual = select.value;

                select.innerHTML = '<option value="">-- Selecciona un folio --</option>';
                folios.forEach(folio => {
                    const option = document.createElement('option');
                    option.value = folio.id;
                    const numero = 'GD' + String(folio.numero).padStart(4, '0');
                    const impreso = folio.impreso_at
                        ? ' — impreso ' + new Date(folio.impreso_at).toLocaleString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
                        : '';
                    option.textContent = numero + impreso;
                    select.appendChild(option);
                });

                if (folios.some(f => String(f.id) === seleccionActual)) {
                    select.value = seleccionActual;
                }

                actualizarEstadoFolio();
            });
    }

    document.getElementById('check-sin-folio')?.addEventListener('change', actualizarEstadoFolio);
    document.getElementById('modalCrearCliente')?.addEventListener('show.bs.modal', refrescarFoliosDisponibles);
</script>
@endpush
