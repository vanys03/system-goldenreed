@push('scripts')

<script>

    const tablaClientes = $('#tabla-clientes');

    if (tablaClientes.length) {

        tablaClientes.DataTable({

            pageLength: 10,

            deferRender: true,

            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },

            order: [],

            initComplete: function () {

                $('#loader-clientes').remove();

                tablaClientes.removeClass('d-none');
            }
        });
    }

</script>


<script>

$(document).on('click', '.toggle-estado', function() {

    const id = $(this).data('id');

    $.ajax({

        url: `/clientes/${id}/toggle-estado`,

        method: 'POST',

        data: {
            _token: '{{ csrf_token() }}'
        },

        success: function(resp) {

            location.reload();
        }
    });
});


$(document).on('change', '.change-tipo', function() {

    const id = $(this).data('id');

    const tipo = $(this).val();

    $.ajax({

        url: `/clientes/${id}/update-tipo`,

        method: 'POST',

        data: {

            _token: '{{ csrf_token() }}',

            tipo: tipo
        },

        success: function(resp) {

            location.reload();
        }
    });
});

</script>

@endpush