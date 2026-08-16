<x-confirm-modal
    id="modalEliminarAnydesk{{ $anydesk->id }}"
    title="Confirmar eliminación"
    message="¿Estás seguro de que deseas eliminar el acceso AnyDesk {{ $anydesk->nombre }} ({{ $anydesk->torre }})?"
    icon="delete_forever"
    confirmText="Eliminar"
    cancelText="Cancelar"
    confirmClass="btn-exit"
    cancelClass="btn-cancel"
    :onConfirm="'document.getElementById(\'formEliminarAnydesk' . $anydesk->id . '\').submit();'"
/>

<form id="formEliminarAnydesk{{ $anydesk->id }}" action="{{ route('anydesks.destroy', $anydesk->id) }}" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>
