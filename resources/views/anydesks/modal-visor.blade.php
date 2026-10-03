<!-- Visor de imágenes de un acceso AnyDesk (uno solo, compartido por toda la tabla) -->
<div class="modal fade" id="modalVisorAnydesk" tabindex="-1" aria-labelledby="modalVisorAnydeskLabel"
    aria-hidden="true" translate="no">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient-dark">
                <h5 class="modal-title fw-bold d-flex align-items-center text-white" id="modalVisorAnydeskLabel">
                    <i class="material-icons me-2 text-white">image</i>
                    <span id="visorTitulo">Imágenes del acceso</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"
                    style="filter: invert(1);"></button>
            </div>

            <div class="modal-body bg-dark d-flex align-items-center justify-content-center position-relative p-0"
                style="min-height: 60vh;">
                <button type="button" class="btn btn-light rounded-circle position-absolute start-0 ms-3 shadow"
                    id="visorAnterior" title="Anterior" style="z-index: 2;">
                    <span class="material-icons align-middle">chevron_left</span>
                </button>

                <img id="visorImagen" src="" alt="Imagen del acceso AnyDesk"
                    style="max-width: 100%; max-height: 75vh; object-fit: contain;">

                <button type="button" class="btn btn-light rounded-circle position-absolute end-0 me-3 shadow"
                    id="visorSiguiente" title="Siguiente" style="z-index: 2;">
                    <span class="material-icons align-middle">chevron_right</span>
                </button>
            </div>

            <div class="modal-footer justify-content-between">
                <span class="text-secondary text-sm" id="visorContador"></span>
                <div>
                    <a href="#" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm" id="visorAbrir">
                        <span class="material-icons align-middle" style="font-size: 16px;">open_in_new</span>
                        Abrir en pestaña
                    </a>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>
