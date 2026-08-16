@push('scripts')
<script>
    const tablaAnydesks = $('#tabla-anydesks');
    if (tablaAnydesks.length) {
        tablaAnydesks.DataTable({
            pageLength: 10,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-MX.json',
                emptyTable: 'No hay accesos AnyDesk registrados.'
            },
            columnDefs: [
                { orderable: false, targets: 3 },
                { orderable: false, targets: 4 }
            ],
            order: [],
            initComplete: function () {
                tablaAnydesks.removeClass('d-none');
            }
        });
    }

    // Mostrar/ocultar contraseña en la tabla
    document.addEventListener('click', function (e) {
        const toggleBtn = e.target.closest('.anydesk-toggle-secret');
        if (toggleBtn) {
            const wrapper = toggleBtn.closest('td');
            const span = wrapper.querySelector('.anydesk-secret');
            const icon = toggleBtn.querySelector('.material-icons');
            const oculto = span.textContent.trim() === '••••••••';
            span.textContent = oculto ? span.dataset.value : '••••••••';
            icon.textContent = oculto ? 'visibility_off' : 'visibility';
        }

        // Mostrar/ocultar contraseña dentro de los modales de crear/editar
        const togglePass = e.target.closest('.anydesk-toggle-password');
        if (togglePass) {
            const input = togglePass.closest('.input-group').querySelector('.anydesk-password-input');
            const icon = togglePass.querySelector('.material-icons');
            const esPassword = input.type === 'password';
            input.type = esPassword ? 'text' : 'password';
            icon.textContent = esPassword ? 'visibility_off' : 'visibility';
        }

        // Copiar código o contraseña al portapapeles
        const copyBtn = e.target.closest('.anydesk-copy');
        if (copyBtn) {
            const valor = copyBtn.dataset.copy;
            const copiar = (texto) => {
                if (navigator.clipboard && window.isSecureContext) {
                    return navigator.clipboard.writeText(texto);
                }
                const temp = document.createElement('textarea');
                temp.value = texto;
                temp.style.position = 'fixed';
                temp.style.opacity = '0';
                document.body.appendChild(temp);
                temp.select();
                document.execCommand('copy');
                document.body.removeChild(temp);
                return Promise.resolve();
            };

            copiar(valor).then(() => {
                const icon = copyBtn.querySelector('.material-icons');
                const original = icon.textContent;
                icon.textContent = 'check';
                setTimeout(() => { icon.textContent = original; }, 1200);
            });
        }
    });
</script>
@endpush
