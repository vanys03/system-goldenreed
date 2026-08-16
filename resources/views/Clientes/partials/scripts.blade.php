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
</script>
@endpush
