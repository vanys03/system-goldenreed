@push('styles')
<style>
    /* Se incluye una sola vez: las modales de editar se repiten por fila. */
    .anydesk-form .icon-shape-sm {
        width: 40px;
        height: 40px;
        min-width: 40px;
    }

    .anydesk-form .form-control,
    .anydesk-form .form-select {
        border-color: #dee2e6;
    }

    .anydesk-form .form-control:focus,
    .anydesk-form .form-select:focus {
        border-color: #adb5bd;
        box-shadow: none;
    }

    /* Campo de contraseña con el ojo encima del input, sin prefijo de icono. */
    .anydesk-campo-pass {
        position: relative;
    }

    .anydesk-campo-pass .anydesk-password-input {
        padding-right: 2.25rem;
    }

    .anydesk-campo-pass .anydesk-toggle-password {
        position: absolute;
        top: 50%;
        right: 2px;
        transform: translateY(-50%);
        padding: 0 .35rem;
        line-height: 1;
    }

    .anydesk-campo-pass .anydesk-toggle-password .material-icons {
        font-size: 18px;
    }

    /* Panel gris que agrupa las imágenes, igual que el de documentos del cliente. */
    .anydesk-panel {
        background: #f8f9fa;
    }

    .anydesk-imagen-guardada img {
        transition: opacity .15s ease, filter .15s ease;
    }

    /* El tema pone "border: none" y fondo blanco en los checkboxes, asi que sin
       marcar no se ven. Se le devuelve el borde y se marca en rojo al activarse,
       porque este check significa borrar. */
    .anydesk-form .form-check-input {
        border: 1px solid #8392ab;
        background-color: #fff;
        cursor: pointer;
    }

    .anydesk-form .form-check-input:hover {
        border-color: #d32f2f;
    }

    .anydesk-form .form-check-input:checked[type="checkbox"] {
        background-color: #d32f2f;
        border-color: #d32f2f;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='m6 10 3 3 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: center;
        background-size: 70%;
    }

    .anydesk-form .form-check-input:focus {
        border-color: #d32f2f;
        box-shadow: 0 0 0 2px rgba(211, 47, 47, .25);
    }

    .anydesk-imagen-guardada .anydesk-quitar {
        font-size: .68rem;
    }

    /* Aviso verde de "ya tiene imágenes", al estilo de doc-existente. */
    .anydesk-form .anydesk-sin-imagenes {
        background: #fff3cd;
        border: 1px solid #ffe69c;
        color: #997404;
    }
</style>
@endpush
