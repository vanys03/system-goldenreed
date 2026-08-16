<?php

namespace App\Http\Controllers;

use App\Models\Renta;
use App\Models\ClienteRenta;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Routing\Controller;

class RentasController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Ver rentas')->only(['index', 'show', 'detalle', 'historial', 'data']);
        $this->middleware('permission:Crear rentas')->only('store');
        $this->middleware('permission:Editar rentas')->only(['update', 'confirmar', 'cancelar']);
        $this->middleware('permission:Eliminar rentas')->only('destroy');
    }

    public function index()
    {
        $rentas = Renta::with(['cliente', 'usuario'])->get();
        return view('rentas.index', compact('rentas'));
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'cliente_renta_id' => 'required|exists:clientes_rentas,id',
        'meses' => 'required|integer|min:1|max:12',
        'descuento' => 'nullable|numeric|min:0',
        'tipo_pago' => 'required|in:Efectivo,Transferencia',
    ]);

    $cliente = ClienteRenta::findOrFail($validated['cliente_renta_id']);
    $precio = $cliente->precio ?? 300;

    $meses = (int) $validated['meses'];

    $fechaInicio = Carbon::now()->startOfMonth();
    $fechaFin = (clone $fechaInicio)->addMonths($meses)->subDay();

    $clientesExentosLuz = [18, 8, 12];

    $mesActual = Carbon::now()->month;

    if (in_array($cliente->id, $clientesExentosLuz)) {
        $recargoLuz = 0;
    } else {
        $recargoLuz = ($mesActual % 2 !== 0) ? 200 : 0;
    }

    $subtotal = $precio * $meses;
    $descuento = $validated['descuento'] ?? 0;
    $total = $subtotal - $descuento + $recargoLuz;

    $renta = Renta::create([
        'cliente_renta_id' => $cliente->id,
        'user_id' => auth()->id(),
        'meses' => $meses,
        'descuento' => $descuento,
        'recargo_domicilio' => $recargoLuz,
        'fecha_venta' => now()->toDateString(),
        'fecha_inicio' => $fechaInicio,
        'fecha_fin' => $fechaFin,
        'subtotal' => $subtotal,
        'total' => $total,
        'estado' => 'activa',
        'tipo_pago' => $validated['tipo_pago'],
    ]);

    return redirect()
        ->route('rentas.index')
        ->with('success', '✅ La renta se guardó correctamente')
        ->with('renta_id_para_imprimir', $renta->id);
}

    public function show($id)
    {
        return Renta::with(['cliente', 'usuario'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $renta = Renta::findOrFail($id);
        $renta->update($request->all());

        return response()->json($renta);
    }

    public function destroy($id)
    {
        Renta::destroy($id);

        return redirect()
            ->route('rentas_historial.index')
            ->with('success', '✅ La renta se eliminó correctamente');
    }

    public function detalle($id)
    {
        $renta = Renta::with(['cliente', 'usuario'])->findOrFail($id);
        return view('rentas_historial.partials.detalle', compact('renta'));
    }

    public function historial()
    {
        $historial = Renta::with(['cliente', 'usuario'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('rentas_historial.index', compact('historial'));
    }

    public function data()
    {
        $query = Renta::select(
            'rentas.id',
            'rentas.meses',
            'rentas.recargo_domicilio',
            'rentas.total',
            'rentas.fecha_venta',
            'clientes_rentas.nombre as cliente',
            'users.name as usuario'
        )
            ->join('clientes_rentas', 'clientes_rentas.id', '=', 'rentas.cliente_renta_id')
            ->join('users', 'users.id', '=', 'rentas.user_id');

        return DataTables::eloquent($query)

            ->editColumn('cliente', fn($r) =>
                '<h6 class="mb-0 text-xs">' . e($r->cliente) . '</h6>'
            )

            ->editColumn('usuario', fn($r) =>
                '<p class="text-xs mb-0">' . e($r->usuario) . '</p>'
            )

            ->editColumn('meses', fn($r) =>
                '<p class="text-xs mb-0 text-center">' . $r->meses . '</p>'
            )

            ->addColumn('recargo', fn($r) =>
                '<p class="text-xs mb-0 text-center">$' . number_format($r->recargo_domicilio, 2) . '</p>'
            )

            ->editColumn('total', fn($r) =>
                '<p class="text-xs mb-0 fw-bold text-center">$' . number_format($r->total, 2) . '</p>'
            )

            ->editColumn('fecha_venta', fn($r) =>
                '<p class="text-xs text-secondary mb-0 text-center">' .
                Carbon::parse($r->fecha_venta)->format('d/m/Y') .
                '</p>'
            )

            ->addColumn('acciones', fn($r) => '
                <button class="btn btn-link text-info p-0 mx-1 ver-renta" data-id="' . $r->id . '">
                    <span class="material-icons">visibility</span>
                </button>

                <button class="btn btn-link text-danger p-0 mx-1 eliminar-renta" data-id="' . $r->id . '">
                    <span class="material-icons">delete_forever</span>
                </button>
            ')

            ->rawColumns([
                'cliente',
                'usuario',
                'meses',
                'recargo',
                'total',
                'fecha_venta',
                'acciones'
            ])
            ->make(true);
    }
}
