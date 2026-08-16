<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Cliente;
use App\Models\Contador;
use App\Models\Paquete;
use App\Services\ContratoPdfService;
use Yajra\DataTables\Facades\DataTables;

class ClientesController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Ver clientes')->only(['index', 'data', 'show', 'contrato', 'contratoBlanco']);
        $this->middleware('permission:Crear clientes')->only(['create', 'store']);
        $this->middleware('permission:Editar clientes')->only(['edit', 'update']);
        $this->middleware('permission:Eliminar clientes')->only('destroy');
    }

    public function index()
    {
        $paquetes = Paquete::all();

        return view('clientes.index', compact('paquetes'));
    }

    public function data()
    {
        $query = Cliente::select(['id', 'nombre', 'telefono1', 'telefono2', 'dia_cobro', 'referencias', 'tipo', 'activo']);

        return DataTables::eloquent($query)
            ->editColumn('nombre', function ($cliente) {
                $colorClass = $cliente->tipo === 'A' ? 'text-danger' : ($cliente->tipo === 'C' ? 'text-info' : '');
                return '<h6 class="mb-0 text-xs ' . $colorClass . '">' . e($cliente->nombre) . '</h6>';
            })
            ->editColumn('telefono1', fn($cliente) => '<p class="text-xs mb-0">' . e($cliente->telefono1) . '</p>')
            ->editColumn('telefono2', fn($cliente) => '<p class="text-xs mb-0">' . e($cliente->telefono2) . '</p>')
            ->editColumn('dia_cobro', fn($cliente) => '<p class="text-xs mb-0 text-warning">' . e($cliente->dia_cobro) . '</p>')
            ->editColumn('referencias', fn($cliente) => '<p class="text-xs mb-0" style="white-space: normal;">' . e($cliente->referencias) . '</p>')
            ->editColumn('tipo', fn($cliente) => '<p class="text-xs mb-0" style="white-space: normal;">' . e($cliente->tipo) . '</p>')
            ->addColumn('acciones', function ($cliente) {
                $html = '<a class="btn btn-link text-dark p-0 mx-1" title="Imprimir contrato" href="'
                    . route('clientes.contrato', $cliente->id) . '" target="_blank">'
                    . '<span class="material-icons">description</span></a>';

                $html .= '<button class="btn btn-link text-success p-0 mx-1 btn-modal" title="Editar" data-url="'
                    . route('clientes.edit-modal', $cliente->id) . '">'
                    . '<span class="material-icons">edit</span></button>';

                if (auth()->user()->can('Eliminar clientes')) {
                    $html .= '<button class="btn btn-link text-danger p-0 mx-1 btn-modal" title="Eliminar" data-url="'
                        . route('clientes.delete-modal', $cliente->id) . '">'
                        . '<span class="material-icons">delete</span></button>';
                }

                return $html;
            })
            ->addColumn('estado', fn($cliente) => $cliente->activo
                ? '<span class="badge bg-success text-white text-xs">Activo</span>'
                : '<span class="badge bg-secondary text-white text-xs">Inactivo</span>')
            ->orderColumn('estado', 'activo $1')
            ->rawColumns(['nombre', 'telefono1', 'telefono2', 'dia_cobro', 'referencias', 'tipo', 'acciones', 'estado'])
            ->make(true);
    }

    public function create()
    {
        $paquetes = Paquete::all();

        return view('clientes.create', compact('paquetes'));
    }

    public function editModal($id)
    {
        $cliente = Cliente::findOrFail($id);
        $paquetes = Paquete::select('id', 'nombre', 'precio')->get();

        return view('clientes.partials.modal-edit', compact('cliente', 'paquetes'));
    }

    public function deleteModal($id)
    {
        $cliente = Cliente::findOrFail($id);

        return view('clientes.partials.modal-delete', compact('cliente'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'tipo' => 'B'
        ]);

        $request->validate([
            'nombre' => 'required|string|max:120',
            'telefono1' => 'nullable|string|max:20',
            'telefono2' => 'nullable|string|max:20',
            'fecha_contrato' => 'required|date',
            'dia_cobro' => 'required|integer|min:1|max:31',
            'paquete_id' => 'nullable|exists:paquetes,id',
            'Mac' => 'nullable|string|max:255',
            'IP' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'coordenadas' => 'nullable|string|max:60',
            'referencias' => 'nullable|string',
            'torre' => 'nullable|string|max:255',
            'panel' => 'nullable|string|max:255',
            'activo' => 'nullable|boolean',
            'zona' => 'nullable|string|max:255',
            'tipo' => 'required|in:A,B,C',
            'documento' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:20480',
        ]);

        $data = $request->except('documento');
        $data['activo'] = true;

        $data['folio'] = Contador::siguienteFolioCliente();

        $cliente = Cliente::create($data);

        if ($request->hasFile('documento')) {

            $archivo = $request->file('documento');

            $nombre = pathinfo(
                $archivo->getClientOriginalName(),
                PATHINFO_FILENAME
            );

            $nombre = Str::slug($nombre);

            $extension = $archivo->getClientOriginalExtension();

            $nombreFinal = time() . '_' . $nombre . '.' . $extension;

            $path = $archivo->storeAs(
                "documentos/{$cliente->id}",
                $nombreFinal,
                'public'
            );

            $cliente->update([
                'documento' => $path
            ]);
        }

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente creado exitosamente.')
            ->with('contrato_url', route('clientes.contrato', $cliente->id));
    }

    public function contrato(Cliente $cliente, ContratoPdfService $contratoPdfService)
    {
        $pdf = $contratoPdfService->generar($cliente);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="contrato-' . $cliente->id . '.pdf"',
        ]);
    }

    public function contratoBlanco(ContratoPdfService $contratoPdfService)
    {
        $siguienteFolio = Contador::previewSiguienteFolioCliente();
        $pdf = $contratoPdfService->generarBlanco($siguienteFolio);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="contrato-en-blanco.pdf"',
        ]);
    }

    public function edit($id)
    {
        $cliente = Cliente::findOrFail($id);
        $paquetes = Paquete::all();

        return view('clientes.edit', compact('cliente', 'paquetes'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:120',
            'telefono1' => 'nullable|string|max:20',
            'telefono2' => 'nullable|string|max:20',
            'fecha_contrato' => 'required|date',
            'dia_cobro' => 'required|integer|min:1|max:31',
            'paquete_id' => 'nullable|exists:paquetes,id',
            'Mac' => 'nullable|string|max:255',
            'IP' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'coordenadas' => 'nullable|string|max:60',
            'referencias' => 'nullable|string',
            'torre' => 'nullable|string|max:255',
            'panel' => 'nullable|string|max:255',
            'activo' => 'nullable|boolean',
            'zona' => 'nullable|string|max:255',
            'tipo' => 'required|in:A,B,C',
            'documento' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:20480',
        ]);

        $cliente = Cliente::findOrFail($id);

        $cliente->update($request->only([
            'nombre',
            'telefono1',
            'telefono2',
            'fecha_contrato',
            'dia_cobro',
            'paquete_id',
            'Mac',
            'IP',
            'direccion',
            'coordenadas',
            'referencias',
            'torre',
            'panel',
            'activo',
            'zona',
            'tipo',
        ]));

        if ($request->hasFile('documento')) {

            if (
                $cliente->documento &&
                Storage::disk('public')->exists($cliente->documento)
            ) {
                Storage::disk('public')->delete($cliente->documento);
            }

            $archivo = $request->file('documento');

            $nombre = pathinfo(
                $archivo->getClientOriginalName(),
                PATHINFO_FILENAME
            );

            $nombre = Str::slug($nombre);

            $extension = $archivo->getClientOriginalExtension();

            $nombreFinal = time() . '_' . $nombre . '.' . $extension;

            $path = $archivo->storeAs(
                "documentos/{$id}",
                $nombreFinal,
                'public'
            );

            $cliente->update([
                'documento' => $path
            ]);
        }

        if ($request->has('equipo')) {

            $equipoData = $request->input('equipo');

            $filteredEquipo = array_map(function ($value) {
                return $value === '' ? null : $value;
            }, $equipoData);

            $hasData = collect($filteredEquipo)
                ->filter(function ($value) {
                    return !is_null($value);
                })
                ->isNotEmpty();

            if ($hasData) {

                $cliente->equipos()->updateOrCreate(
                    ['cliente_id' => $cliente->id],
                    array_merge(
                        $filteredEquipo,
                        ['cliente_id' => $cliente->id]
                    )
                );

            } else {

                $equipoExistente = $cliente->equipos()
                    ->where('cliente_id', $cliente->id)
                    ->first();

                if ($equipoExistente) {
                    $equipoExistente->delete();
                }
            }
        }

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente y equipo actualizados correctamente.');
    }

    public function destroy($id)
    {
        $cliente = Cliente::findOrFail($id);

        if ($cliente->ventas()->exists()) {

            return back()->with(
                'error',
                'No se puede eliminar el cliente porque tiene ventas registradas.'
            );
        }

        if (
            $cliente->documento &&
            Storage::disk('public')->exists($cliente->documento)
        ) {
            Storage::disk('public')->delete($cliente->documento);
        }

        $cliente->delete();

        return back()->with(
            'success',
            'Cliente eliminado correctamente.'
        );
    }

    public function deshabilitar(Cliente $cliente)
    {
        $cliente->update([
            'activo' => 0
        ]);

        return back()->with(
            'success',
            'Cliente deshabilitado correctamente.'
        );
    }
    public function estadoClientes(Request $request)
    {
        $query = Cliente::query();

        // Filtro por rango de fechas de alta (fecha_contrato)
        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('fecha_contrato', [$request->fecha_inicio, $request->fecha_fin]);
        }

        // Filtro por estado (activo/inactivo)
        if ($request->filled('estado') && $request->estado !== 'Todos') {
            $activo = $request->estado === 'Activos' ? 1 : 0;
            $query->where('activo', $activo);
        }

        // Filtro por tipo de cliente (A/B/C)
        if ($request->filled('tipo') && $request->tipo !== 'Todos') {
            $query->where('tipo', $request->tipo);
        }

        $clientes = $query->get();

        // Métricas rápidas
        $total = $clientes->count();
        $activos = $clientes->where('activo', 1)->count();
        $inactivos = $clientes->where('activo', 0)->count();

        // Solo contar clientes activos por tipo
        $tipoA = $clientes->where('activo', 1)->where('tipo', 'A')->count();
        $tipoB = $clientes->where('activo', 1)->where('tipo', 'B')->count();
        $tipoC = $clientes->where('activo', 1)->where('tipo', 'C')->count();

        return view('estado_clientes.index', compact(
            'clientes',
            'total',
            'activos',
            'inactivos',
            'tipoA',
            'tipoB',
            'tipoC'
        ));
    }
    public function toggleEstado(Cliente $cliente)
    {
        $cliente->update(['activo' => !$cliente->activo]);
        return response()->json(['success' => true]);
    }

    public function updateTipo(Request $request, Cliente $cliente)
    {
        $request->validate(['tipo' => 'required|in:A,B,C']);
        $cliente->update(['tipo' => $request->tipo]);
        return response()->json(['success' => true]);
    }
    public function reporteClientes(Request $request)
    {
        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;
        $tipo = $request->tipo;
        $nuevosQuery = Cliente::query();
        if ($fechaInicio && $fechaFin) {
            $nuevosQuery->whereBetween('fecha_contrato', [
                $fechaInicio,
                $fechaFin
            ]);
        }
        if ($tipo && $tipo !== 'Todos') {
            $nuevosQuery->where('tipo', $tipo);
        }

        $clientesNuevos = $nuevosQuery->count();
        $inactivosQuery = Cliente::where('activo', 0);

        if ($fechaInicio && $fechaFin) {
            $inactivosQuery->whereBetween('updated_at', [
                $fechaInicio,
                $fechaFin
            ]);
        }
        if ($tipo && $tipo !== 'Todos') {
            $inactivosQuery->where('tipo', $tipo);
        }

        $clientesInactivos = $inactivosQuery->count();
        $tiposInactivos = Cliente::selectRaw('tipo, COUNT(*) as total')
            ->where('activo', 0)
            ->when($fechaInicio && $fechaFin, function ($query) use ($fechaInicio, $fechaFin) {
                $query->whereBetween('updated_at', [
                    $fechaInicio,
                    $fechaFin
                ]);
            })
            ->groupBy('tipo')
            ->pluck('total', 'tipo');
        $activos = Cliente::where('activo', 1)
            ->when($tipo && $tipo !== 'Todos', function ($query) use ($tipo) {
                $query->where('tipo', $tipo);
            })
            ->count();
        $clientes = Cliente::query()
            ->when($tipo && $tipo !== 'Todos', function ($query) use ($tipo) {
                $query->where('tipo', $tipo);
            })
            ->latest()
            ->get();

        return view('estado_clientes.index', [
            'clientes' => $clientes,

            'clientesNuevos' => $clientesNuevos,
            'clientesInactivos' => $clientesInactivos,
            'activos' => $activos,

            'tipoAInactivos' => $tiposInactivos['A'] ?? 0,
            'tipoBInactivos' => $tiposInactivos['B'] ?? 0,
            'tipoCInactivos' => $tiposInactivos['C'] ?? 0,
        ]);
    }

}