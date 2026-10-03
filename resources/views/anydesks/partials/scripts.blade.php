@push('scripts')
<script>
    const MAX_IMAGENES = 8;
    const MAX_PESO_MB = 4;
    // Margen bajo el post_max_size de PHP ({{ ini_get('post_max_size') }}) para el formulario.
    const MAX_POST_MB = 36;

    const tablaAnydesks = $('#tabla-anydesks');
    if (tablaAnydesks.length) {
        tablaAnydesks.DataTable({
            pageLength: 10,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-MX.json',
                emptyTable: 'No hay accesos AnyDesk registrados.'
            },
            columnDefs: [
                { orderable: false, searchable: false, targets: 0 },
                { orderable: false, searchable: false, targets: 3 },
                { orderable: false, searchable: false, targets: 4 }
            ],
            order: [],
            initComplete: function () {
                tablaAnydesks.removeClass('d-none');
            }
        });
    }

    // ── Visor de imágenes ────────────────────────────────────────────────
    const visor = {
        urls: [],
        indice: 0,

        abrir(urls, indice, titulo) {
            this.urls = urls;
            this.indice = indice;
            document.getElementById('visorTitulo').textContent = titulo || 'Imágenes del acceso';
            this.pintar();
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalVisorAnydesk')).show();
        },

        pintar() {
            const url = this.urls[this.indice];
            document.getElementById('visorImagen').src = url;
            document.getElementById('visorAbrir').href = url;
            document.getElementById('visorContador').textContent =
                'Imagen ' + (this.indice + 1) + ' de ' + this.urls.length;

            // Con una sola imagen las flechas no aportan nada.
            const soloUna = this.urls.length < 2;
            document.getElementById('visorAnterior').hidden = soloUna;
            document.getElementById('visorSiguiente').hidden = soloUna;
        },

        mover(paso) {
            if (!this.urls.length) return;
            this.indice = (this.indice + paso + this.urls.length) % this.urls.length;
            this.pintar();
        }
    };

    document.getElementById('visorAnterior').addEventListener('click', () => visor.mover(-1));
    document.getElementById('visorSiguiente').addEventListener('click', () => visor.mover(1));

    // Flechas del teclado mientras el visor está abierto.
    document.addEventListener('keydown', function (e) {
        const abierto = document.getElementById('modalVisorAnydesk').classList.contains('show');
        if (!abierto) return;
        if (e.key === 'ArrowLeft') visor.mover(-1);
        if (e.key === 'ArrowRight') visor.mover(1);
    });

    // ── Vista previa de los archivos elegidos ────────────────────────────
    document.addEventListener('change', function (e) {
        const input = e.target.closest('.anydesk-input-imagenes');
        if (!input) return;

        const preview = input.closest('.anydesk-bloque-imagenes').querySelector('.anydesk-preview');
        preview.innerHTML = '';

        const archivos = Array.from(input.files || []);
        if (!archivos.length) return;

        const avisos = [];
        if (archivos.length > MAX_IMAGENES) {
            avisos.push('Seleccionaste ' + archivos.length + ' imágenes; el máximo es ' + MAX_IMAGENES + '.');
        }

        const pesadas = archivos.filter(a => a.size > MAX_PESO_MB * 1024 * 1024);
        if (pesadas.length) {
            avisos.push(pesadas.length + ' imagen(es) pasan de ' + MAX_PESO_MB + ' MB y serán rechazadas.');
        }

        // PHP descarta el POST completo si pasa de post_max_size, y eso se ve como
        // un error de sesión en lugar de un error de validación. Mejor avisar antes.
        const totalMb = archivos.reduce((suma, a) => suma + a.size, 0) / (1024 * 1024);
        if (totalMb > MAX_POST_MB) {
            avisos.push('En total pesan ' + totalMb.toFixed(1) + ' MB y el servidor acepta ' +
                MAX_POST_MB + ' MB por envío. Sube menos imágenes a la vez.');
        }

        if (avisos.length) {
            const alerta = document.createElement('div');
            alerta.className = 'alert alert-warning text-xs py-2 px-3 mb-2 w-100';
            alerta.textContent = avisos.join(' ');
            preview.appendChild(alerta);
        }

        archivos.forEach(function (archivo) {
            const img = document.createElement('img');
            img.className = 'rounded border';
            img.style.cssText = 'width: 72px; height: 72px; object-fit: cover;';
            img.title = archivo.name;
            img.src = URL.createObjectURL(archivo);
            img.addEventListener('load', () => URL.revokeObjectURL(img.src), { once: true });
            preview.appendChild(img);
        });
    });

    document.addEventListener('click', function (e) {
        // Abrir el visor desde una miniatura o desde el badge "+N"
        const thumb = e.target.closest('.anydesk-thumb');
        if (thumb) {
            visor.abrir(
                JSON.parse(thumb.dataset.galeria),
                parseInt(thumb.dataset.indice, 10) || 0,
                thumb.dataset.titulo
            );
            return;
        }

        // Mostrar/ocultar contraseña en la tabla
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
            const input = togglePass.closest('.anydesk-campo-pass').querySelector('.anydesk-password-input');
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

    // Atenuar la imagen marcada para borrado, para que se vea qué se va a perder al guardar.
    document.addEventListener('change', function (e) {
        const check = e.target.closest('.anydesk-marcar-borrado');
        if (!check) return;

        const img = check.closest('.anydesk-imagen-guardada').querySelector('img');
        img.style.opacity = check.checked ? '0.35' : '1';
        img.style.filter = check.checked ? 'grayscale(1)' : 'none';
    });
</script>
@endpush
