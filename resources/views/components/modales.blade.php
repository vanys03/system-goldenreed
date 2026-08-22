@push('scripts')
<script>
    $(document).on('click', '.btn-modal', function () {
        const url = $(this).data('url');
        $('#contenido-modal').html('<div class="text-center p-4">Cargando...</div>');

        $.get(url, function (html) {
            $('#contenido-modal').addClass('border-0 shadow-lg rounded-4').html(html);
            const dialog = $('#modal-dialog-dinamico');

            if (url.includes('delete')) {
                dialog.removeClass('modal-dialog-centered modal-lg modal-xl').addClass('modal-dialog-centered').css('max-width', '500px');
            } else if (url.includes('edit')) {
                dialog.removeClass('modal-dialog-centered modal-lg').addClass('modal-xl').css('max-width', '');
            } else {
                dialog.removeClass('modal-dialog-centered modal-xl').addClass('modal-lg').css('max-width', '');
            }

            $('#modalDinamico').modal('show');
        });
    });
</script>
@endpush
