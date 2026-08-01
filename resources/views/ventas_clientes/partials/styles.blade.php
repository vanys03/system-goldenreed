@push('styles')


<style>

.table td,
.table th{
    white-space: nowrap;
    vertical-align: middle;
}

.badge{
    font-size: .75rem;
}

.table tbody tr:hover{
    background: #f8f9fa;
}

.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter,
.dataTables_wrapper .dataTables_info,
.dataTables_wrapper .dataTables_paginate{
    margin: .75rem 0;
    font-size: .8125rem;
    color: #67748e;
}

.dataTables_wrapper .dataTables_filter input,
.dataTables_wrapper .dataTables_length select{
    border-radius: .5rem;
    border: 1px solid #d2d6da;
    padding: .25rem .5rem;
}

.dataTables_wrapper .dataTables_paginate .pagination{
    gap: .35rem;
}

.dataTables_wrapper .dataTables_paginate .page-item .page-link{
    width: 2rem;
    height: 2rem;
    padding: 0;
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    border: 1px solid #d2d6da;
}

.dataTables_wrapper .dataTables_paginate .page-item.disabled .page-link{
    opacity: .5;
}

.dataTables_wrapper .dataTables_paginate .page-item.previous .page-link,
.dataTables_wrapper .dataTables_paginate .page-item.next .page-link{
    font-size: 0;
}

.dataTables_wrapper .dataTables_paginate .page-item.previous .page-link::before{
    content: "\2039";
    font-size: 1.15rem;
    line-height: 1;
}

.dataTables_wrapper .dataTables_paginate .page-item.next .page-link::before{
    content: "\203A";
    font-size: 1.15rem;
    line-height: 1;
}

.fila-cliente:hover{
    background: #f8f9fa;
}

.timeline-pagos .pago-item{
    position: relative;
    padding-bottom: 1rem;
}

.timeline-pagos .pago-item:not(:last-child)::before{
    content: "";
    position: absolute;
    left: 4px;
    top: 14px;
    bottom: -2px;
    width: 1px;
    background: #e9ecef;
}

.timeline-pagos .dot-estado{
    width: 9px;
    height: 9px;
    min-width: 9px;
    border-radius: 50%;
    margin-right: .75rem;
    margin-top: .35rem;
}

.icon-shape-sm{
    width: 40px;
    height: 40px;
    min-width: 40px;
}

</style>
@endpush