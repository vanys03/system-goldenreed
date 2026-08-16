@push('scripts')
<script>
    const tablaRoles = $('#tabla-roles');
    if (tablaRoles.length) {
        tablaRoles.DataTable({
            pageLength: 10,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-MX.json'
            },
            columnDefs: [
                { orderable: false, targets: 2 }
            ],
            order: [],
            initComplete: function () {
                tablaRoles.removeClass('d-none');
            }
        });
    }

    // Matriz de permisos: toggles de columna, fila y "todo" en los modales de crear/editar rol
    document.addEventListener('change', function (e) {
        if (e.target.matches('.perm-all-toggle')) {
            const table = e.target.closest('table');
            table.querySelectorAll('.perm-check, .perm-col-toggle, .perm-row-toggle')
                .forEach(cb => cb.checked = e.target.checked);
        }

        if (e.target.matches('.perm-col-toggle')) {
            const table = e.target.closest('table');
            const accion = e.target.dataset.accion;
            table.querySelectorAll('.perm-check[data-accion="' + accion + '"]')
                .forEach(cb => cb.checked = e.target.checked);
        }

        if (e.target.matches('.perm-row-toggle')) {
            const row = e.target.closest('tr');
            row.querySelectorAll('.perm-check').forEach(cb => cb.checked = e.target.checked);
        }
    });
</script>
@endpush
