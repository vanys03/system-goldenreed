<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Venta;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;

class VentasController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Ver ventas')->only([
            'index', 'calcularRecargo', 'buscarClientes', 'historial', 'corte',
            'estadoCliente', 'ventasClientes', 'filtrarVentasClientes', 'filtrarCorte',
        ]);
        $this->middleware('permission:Crear ventas')->only(['store', 'pagarPorTransferencia']);
        $this->middleware('permission:Crear adeudos')->only('registrarAdeudo');
        $this->middleware('permission:Editar ventas')->only(['update', 'cerrarCorteHoy']);
        $this->middleware('permission:Eliminar ventas')->only('destroy');
    }

    public function index(Request $request)
    {
        $clientes = Cliente::with([
            'paquete',
            'ventas' => fn($q) => $q->orderBy('fecha_venta', 'desc')->limit(1)
        ])->get();

        $ventasHoy = Venta::with(['cliente', 'usuario'])
            ->whereDate('fecha_venta', today())
            ->orderBy('created_at', 'desc')
            ->get();

        $ventaEditar = null;
        if ($request->has('editar')) {
            $ventaEditar = Venta::with('cliente.paquete')->find($request->editar);
        }

        return view('ventas.index', compact('clientes', 'ventasHoy', 'ventaEditar'));
    }


    public function calcularRecargo(Cliente $cliente)
    {
        [$diasAtraso, $recargo_falta_pago] = $this->obtenerAtrasoYRecargo($cliente);

        return response()->json([
            'recargo' => $recargo_falta_pago,
            'dias_atraso' => $diasAtraso,
        ]);
    }

    public function buscarClientes(Request $request)
    {
        $search = $request->input('q');

        $clientes = Cliente::with('paquete')
            ->where('nombre', 'like', "%{$search}%")
            ->limit(10)
            ->get();

        $resultados = $clientes->map(function ($cliente) {
            return [
                'id' => $cliente->id,
                'text' => $cliente->nombre,
                'tipo' => $cliente->tipo,
                'dia_pago' => $cliente->dia_cobro,
                'paquete' => [
                    'nombre' => $cliente->paquete->nombre ?? 'Sin paquete',
                    'precio' => $cliente->paquete->precio ?? 0,
                ],
            ];
        });

        return response()->json(['results' => $resultados]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'meses' => 'required|integer|min:1|max:12',
            'descuento' => 'nullable|numeric|min:0',
            'recargo_domicilio' => 'nullable|numeric|min:0',
            'tipo_pago' => 'required|in:Efectivo,Transferencia',
        ]);

        $minutosBloqueo = 5;

        $ventaReciente = Venta::where('cliente_id', $request->cliente_id)
            ->where('created_at', '>=', now()->subMinutes($minutosBloqueo))
            ->exists();

        if ($ventaReciente) {
            return redirect()->back()->with(
                'error',
                'Este cliente ya tiene un pago registrado recientemente. Espera unos minutos para volver a intentar.'
            );
        }

        $cliente = Cliente::with('paquete')->findOrFail($request->cliente_id);

        if (!$cliente->paquete) {
            return redirect()->back()->with(
                'error',
                'Este cliente no tiene paquete asignado.'
            );
        }
        $precioPaquete = $cliente->paquete->precio;
        $meses = (int) $request->meses;

        $mesesExtra = 0;
        if ($meses === 6) {
            $mesesExtra = 1;
        } elseif ($meses === 12) {
            $mesesExtra = 2;
        }

        $mesesTotales = $meses + $mesesExtra;
        $subtotal = $precioPaquete * $meses;
        $ultimaVenta = Venta::where('cliente_id', $cliente->id)
            ->orderBy('fecha_venta', 'desc')
            ->first();

        [$diasAtraso, $recargoCalculado] = $this->obtenerAtrasoYRecargo($cliente);

        $total = $subtotal
            - ($request->descuento ?? 0)
            + ($request->recargo_domicilio ?? 0)
            + $recargoCalculado;

        $diaCobro = is_numeric($cliente->dia_cobro) ? (int) $cliente->dia_cobro : 1;

        if ($ultimaVenta) {
            $periodoInicio = Carbon::parse($ultimaVenta->periodo_fin)->startOfDay();
        } else {
            $hoy = now()->startOfDay();
            $proximoCobro = $hoy->copy()->day($diaCobro);

            if ($hoy->day > $diaCobro) {
                $proximoCobro->addMonthNoOverflow();
            }

            $periodoInicio = $proximoCobro;
        }

        $periodoFin = $periodoInicio->copy()
            ->addMonthsNoOverflow($mesesTotales);

        $pagoPeriodoDuplicado = Venta::where('cliente_id', $cliente->id)
            ->whereDate('periodo_inicio', $periodoInicio)
            ->whereDate('periodo_fin', $periodoFin)
            ->exists();

        if ($pagoPeriodoDuplicado) {
            return redirect()->back()->with(
                'error',
                'Ya existe un pago registrado para este mismo periodo.'
            );
        }

        $venta = Venta::create([
            'usuario_id' => Auth::id(),
            'cliente_id' => $cliente->id,
            'estado' => 'pagado',
            'meses' => $meses,
            'descuento' => $request->descuento ?? 0,
            'recargo_domicilio' => $request->recargo_domicilio ?? 0,
            'recargo_atraso' => $recargoCalculado,
            'fecha_venta' => now(),
            'subtotal' => $subtotal,
            'total' => $total,
            'periodo_inicio' => $periodoInicio,
            'periodo_fin' => $periodoFin,
            'tipo_pago' => $request->tipo_pago,
        ]);

        return redirect()
            ->route('ventas.index')
            ->with('venta_id_para_imprimir', $venta->id);
    }

    /**
     * Registra un pago retroactivo desde el módulo de adeudos del dashboard,
     * para meses que no se capturaron a tiempo en el sistema.
     */
    public function registrarAdeudo(Request $request, Cliente $cliente)
    {
        $request->validate([
            'meses_seleccionados' => 'required|array|min:1',
            'meses_seleccionados.*.mes' => 'required|date_format:Y-m',
            'meses_seleccionados.*.tipo_pago' => 'required|in:Efectivo,Transferencia',
        ]);

        $cliente->load('paquete', 'ventas');

        if (!$cliente->paquete) {
            return response()->json(['message' => 'Este cliente no tiene paquete asignado.'], 422);
        }

        $seleccionados = collect($request->meses_seleccionados)
            ->sortBy('mes')
            ->values();

        // Cada venta solo admite un tipo de pago, así que se agrupan los meses
        // consecutivos que comparten el mismo tipo de pago (ej. junio en
        // transferencia y julio en efectivo generan dos ventas separadas).
        $grupos = [];
        $grupoActual = [];

        foreach ($seleccionados as $item) {
            if (empty($grupoActual)) {
                $grupoActual[] = $item;
                continue;
            }

            $ultimo = end($grupoActual);
            $mesEsperado = Carbon::createFromFormat('Y-m', $ultimo['mes'])
                ->addMonthNoOverflow()
                ->format('Y-m');

            if ($item['tipo_pago'] === $ultimo['tipo_pago'] && $item['mes'] === $mesEsperado) {
                $grupoActual[] = $item;
            } else {
                $grupos[] = $grupoActual;
                $grupoActual = [$item];
            }
        }

        if (!empty($grupoActual)) {
            $grupos[] = $grupoActual;
        }

        $precioPaquete = $cliente->paquete->precio;
        $ventasCreadas = 0;

        foreach ($grupos as $grupo) {
            $meses = count($grupo);
            $periodoInicio = Carbon::createFromFormat('Y-m', $grupo[0]['mes'])->startOfMonth();
            $periodoFin = $periodoInicio->copy()->addMonthsNoOverflow($meses);

            $pagoPeriodoDuplicado = Venta::where('cliente_id', $cliente->id)
                ->whereDate('periodo_inicio', $periodoInicio)
                ->whereDate('periodo_fin', $periodoFin)
                ->exists();

            if ($pagoPeriodoDuplicado) {
                return response()->json([
                    'message' => 'Ya existe un pago registrado para el periodo que inicia en ' . $periodoInicio->format('m/Y') . '.',
                ], 422);
            }

            $subtotal = $precioPaquete * $meses;

            Venta::create([
                'usuario_id' => Auth::id(),
                'cliente_id' => $cliente->id,
                'estado' => 'pagado',
                'meses' => $meses,
                'descuento' => 0,
                'recargo_domicilio' => 0,
                'recargo_atraso' => 0,
                'fecha_venta' => now(),
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'periodo_inicio' => $periodoInicio,
                'periodo_fin' => $periodoFin,
                'tipo_pago' => $grupo[0]['tipo_pago'],
                'notas' => 'Pago capturado desde el módulo de adeudos del dashboard (mes(es) que no se habían registrado a tiempo en el sistema).',
            ]);

            $ventasCreadas++;
        }

        session()->flash('success', $ventasCreadas === 1
            ? 'Pago registrado correctamente.'
            : "{$ventasCreadas} pagos registrados correctamente.");

        return response()->json([
            'success' => true,
            'ventas_creadas' => $ventasCreadas,
        ]);
    }


    public function historial()
    {
        $ventas = Venta::with(['cliente', 'usuario'])
            ->orderBy('fecha_venta', 'desc')
            ->get();

        return view('ventas_historial.index', compact('ventas'));
    }
    public function corte(Request $request)
    {
        $hoy = now()->startOfDay();

        $usuarios = \App\Models\User::all();
        $tiposClientes = Cliente::select('tipo')->distinct()->pluck('tipo');


        $clientesPendientesHoy = Cliente::where('activo', 1)
            ->where('dia_cobro', $hoy->day)
            ->whereDoesntHave('ventas', function ($q) use ($hoy) {
                $q->where('periodo_fin', '>', $hoy);
            })
            ->with([
                'paquete',
                'ventas' => function ($q) {
                    $q->orderByDesc('periodo_fin')->limit(1);
                }
            ])
            ->get();

        $clientesAtrasados = Cliente::where('activo', 1)
            ->whereHas('ventas')
            ->whereRaw("
        (
            SELECT MAX(periodo_fin)
            FROM ventas
            WHERE ventas.cliente_id = clientes.id
        ) < ?
    ", [$hoy])
            ->with([
                'paquete',
                'ventas' => function ($q) {
                    $q->orderByDesc('periodo_fin')->limit(1);
                }
            ])
            ->orderBy(
                Venta::select('periodo_fin')
                    ->whereColumn('ventas.cliente_id', 'clientes.id')
                    ->orderByDesc('periodo_fin')
                    ->limit(1),
                'asc'
            )
            ->paginate(10);


        if (
            ($clientesPendientesHoy->count() > 0 || $clientesAtrasados->count() > 0)
            && !$request->boolean('forzar_corte')
        ) {
            return view('ventas_corte.index', [
                'clientesPendientesHoy' => $clientesPendientesHoy,
                'clientesPendientesAyer' => $clientesAtrasados,
                'bloquearCorte' => true,
                'usuarios' => $usuarios,
                'tiposClientes' => $tiposClientes,
            ]);
        }

        $fecha = $request->input('fecha', $hoy->toDateString());
        $usuario_id = $request->input('usuario_id');
        $tipo_cliente = $request->input('tipo_cliente');

        $query = Venta::with(['cliente', 'usuario'])
            ->whereDate('created_at', $fecha);

        if ($usuario_id) {
            $query->where('usuario_id', $usuario_id);
        }

        if ($tipo_cliente) {
            $query->whereHas('cliente', function ($q) use ($tipo_cliente) {
                $q->where('tipo', $tipo_cliente);
            });
        }

        $ventas = $query->orderBy('fecha_venta', 'desc')->get();

        $totalEfectivo = $ventas->where('tipo_pago', 'Efectivo')->sum('total');
        $totalTransferencia = $ventas->where('tipo_pago', 'Transferencia')->sum('total');
        $conteoEfectivo = $ventas->where('tipo_pago', 'Efectivo')->count();
        $conteoTransferencia = $ventas->where('tipo_pago', 'Transferencia')->count();

        return view('ventas_corte.index', compact(
            'ventas',
            'usuarios',
            'tiposClientes',
            'totalEfectivo',
            'totalTransferencia',
            'conteoEfectivo',
            'conteoTransferencia'
        ));
    }

    private function obtenerAtrasoYRecargo(Cliente $cliente): array
    {
        $hoy = now()->startOfDay();

        $ventaVigente = Venta::where('cliente_id', $cliente->id)
            ->whereDate('periodo_fin', '>=', $hoy)
            ->orderByDesc('periodo_fin')
            ->first();

        if ($ventaVigente) {
            return [0, 0];
        }


        $ultimaVenta = Venta::where('cliente_id', $cliente->id)
            ->orderByDesc('fecha_venta')
            ->first();

        if (is_null($ultimaVenta)) {
            return [0, 0];
        }

        $fechaBase = Carbon::parse($ultimaVenta->periodo_fin)->startOfDay();
        $primerDiaAtraso = $fechaBase->copy()->addDay();

        $diasAtraso = 0;
        $recargo = 0;

        if ($hoy->gt($fechaBase)) {
            $diasAtraso = $fechaBase->diffInDays($hoy);
            $recargo = $diasAtraso <= 3 ? 40 : 140;
        }

        return [$diasAtraso, $recargo];
    }


    public function estadoCliente($clienteId)
    {
        $cliente = Cliente::with('ventas')->findOrFail($clienteId);
        $hoy = now()->startOfDay();
        $diaCobro = $cliente->dia_cobro ?? 1;

        $ultimaVenta = $cliente->ventas->sortByDesc('fecha_venta')->first();

        if (is_null($ultimaVenta)) {
            return response()->json([
                'estado' => 'nuevo',
                'mensaje' => 'Cliente nuevo. no ha realizado pagos.'
            ]);
        }

        $periodoFin = Carbon::parse($ultimaVenta->periodo_fin)->startOfDay();
        $mesAnio = $periodoFin->locale('es')->isoFormat('MMMM [de] YYYY');
        $mesAnio = ucfirst($mesAnio);

        if ($hoy->lt($periodoFin)) {
            $diasRestantes = $hoy->diffInDays($periodoFin);
            if ($diasRestantes <= 5) {
                return response()->json([
                    'estado' => 'proximo',
                    'mensaje' => "Cliente proximo a pagar. Esta cubierto hasta {$mesAnio}"
                ]);
            } else {
                return response()->json([
                    'estado' => 'corriente',
                    'mensaje' => "Cliente al corriente. Pagado hasta el mes de {$mesAnio}"
                ]);
            }
        } else {
            return response()->json([
                'estado' => 'atrasado',
                'mensaje' => "Cliente con atraso. Su ultimo mes cubierto fue {$mesAnio}"
            ]);
        }
    }

    public function pagarPorTransferencia(Request $request)
    {
        $clientesIds = $request->clientes ?? [];
        $hoy = now()->startOfDay();

        foreach ($clientesIds as $clienteId) {

            $cliente = Cliente::with(['paquete', 'ventas'])->find($clienteId);

            if (!$cliente || !$cliente->paquete) {
                continue;
            }

            $precio = $cliente->paquete->precio;
            $ultimaVenta = $cliente->ventas
                ->sortByDesc('periodo_fin')
                ->first();
            if ($ultimaVenta) {
                $periodoInicio = $ultimaVenta->periodo_fin->copy();
            } else {
                $periodoInicio = $hoy;
            }

            $periodoFin = $periodoInicio->copy()->addMonthNoOverflow();

            Venta::create([
                'usuario_id' => auth()->id(),
                'cliente_id' => $cliente->id,
                'estado' => 'pagado',
                'meses' => 1,
                'descuento' => 0,
                'recargo_domicilio' => 0,
                'recargo_atraso' => 0,
                'fecha_venta' => now(),
                'subtotal' => $precio,
                'total' => $precio,
                'periodo_inicio' => $periodoInicio,
                'periodo_fin' => $periodoFin,
                'tipo_pago' => 'Transferencia',
            ]);
        }

        return redirect()->back()
            ->with('success', 'Pagos por transferencia registrados correctamente.');
    }

    public function update(Request $request, Venta $venta)
    {
        $request->validate([
            'meses' => 'required|integer|min:1|max:12',
            'tipo_pago' => 'required|in:Efectivo,Transferencia',
            'descuento' => 'nullable|numeric|min:0',
            'recargo_domicilio' => 'nullable|numeric|min:0',
            'recargo_falta_pago' => 'nullable|numeric|min:0',
        ]);

        $cliente = $venta->cliente()->with('paquete')->first();

        if (!$cliente || !$cliente->paquete) {
            return redirect()->back()->with('error', 'El cliente no tiene paquete asignado.');
        }

        $precioBase = $cliente->paquete->precio;
        $meses = (int) $request->meses;

        $mesesExtra = 0;
        if ($meses === 6) {
            $mesesExtra = 1;
        } elseif ($meses === 12) {
            $mesesExtra = 2;
        }

        $mesesTotales = $meses + $mesesExtra;

        $subtotal = $precioBase * $meses;
        $total = $subtotal
            - ($request->descuento ?? 0)
            + ($request->recargo_domicilio ?? 0)
            + ($request->recargo_falta_pago ?? 0);

        $ultimaVenta = Venta::where('cliente_id', $cliente->id)
            ->where('id', '!=', $venta->id)
            ->orderBy('fecha_venta', 'desc')
            ->first();

        $diaCobro = is_numeric($cliente->dia_cobro) ? (int) $cliente->dia_cobro : 1;

        if ($ultimaVenta) {
            $periodoInicio = Carbon::parse($ultimaVenta->periodo_fin)->startOfDay();
        } else {
            $hoy = now()->startOfDay();
            $proximoCobro = $hoy->copy()->day($diaCobro);
            if ($hoy->day > $diaCobro) {
                $proximoCobro->addMonthNoOverflow();
            }
            $periodoInicio = $proximoCobro;
        }

        $periodoFin = $periodoInicio->copy()->addMonthsNoOverflow($mesesTotales);

        $venta->update([
            'meses' => $meses,
            'tipo_pago' => $request->tipo_pago,
            'descuento' => $request->descuento ?? 0,
            'recargo_domicilio' => $request->recargo_domicilio ?? 0,
            'recargo_falta_pago' => $request->recargo_falta_pago ?? 0,
            'subtotal' => $subtotal,
            'total' => $total,
            'periodo_inicio' => $periodoInicio,
            'periodo_fin' => $periodoFin,
        ]);

        return redirect()->route('ventas.index')
            ->with('success', 'Venta actualizada correctamente ✨');
    }


    public function destroy($id)
    {
        $venta = Venta::findOrFail($id);
        $venta->delete();

        return redirect()->route('ventas.historial')->with('success', 'Venta eliminada correctamente.');
    }

    public function cerrarCorteHoy(Request $request)
    {
        return redirect()->route('ventas.corte', array_merge(
            $request->only(['fecha', 'usuario_id', 'tipo_cliente']),
            ['forzar_corte' => 1]
        ))->with('info', 'Corte revisado sin registrar transferencias.');
    }

    public function filtrarCorte(Request $request)
    {
        $fecha = $request->fecha ?? now()->toDateString();
        $usuario_id = $request->usuario_id;
        $tipo_cliente = $request->tipo_cliente;

        $query = Venta::with(['cliente', 'usuario'])
            ->whereDate('created_at', $fecha);

        if ($usuario_id) {
            $query->where('usuario_id', $usuario_id);
        }

        if ($tipo_cliente) {
            $query->whereHas('cliente', function ($q) use ($tipo_cliente) {
                $q->where('tipo', $tipo_cliente);
            });
        }

        $ventas = $query->orderBy('fecha_venta', 'desc')->get();

        return response()->json([
            'ventas' => $ventas,
            'totalEfectivo' => $ventas->where('tipo_pago', 'Efectivo')->sum('total'),
            'totalTransferencia' => $ventas->where('tipo_pago', 'Transferencia')->sum('total'),
            'conteoEfectivo' => $ventas->where('tipo_pago', 'Efectivo')->count(),
            'conteoTransferencia' => $ventas->where('tipo_pago', 'Transferencia')->count(),
            'totalGeneral' => $ventas->sum('total'),
        ]);
    }
     public function ventasClientes(Request $request)
    {
        $hoy = Carbon::today();
        $ahora = Carbon::now();

        $estadosPagados = ['cobrado', 'pagado'];
        $totalCobradoDia = Venta::whereIn('estado', $estadosPagados)
            ->whereDate('fecha_venta', $hoy)
            ->sum('total');


        $labelsCobrado = ['Semana 1', 'Semana 2', 'Semana 3', 'Semana 4', 'Semana 5'];
        $dataCobrado = [0, 0, 0, 0, 0];

        $ventasCobradoMes = Venta::whereIn('estado', $estadosPagados)
            ->whereMonth('fecha_venta', $ahora->month)
            ->whereYear('fecha_venta', $ahora->year)
            ->get(['fecha_venta', 'total']);

        foreach ($ventasCobradoMes as $venta) {
            $fecha = Carbon::parse($venta->fecha_venta);
            $indiceSemana = (int) floor(($fecha->day - 1) / 7);

            if ($indiceSemana > 4) {
                $indiceSemana = 4;
            }

            $dataCobrado[$indiceSemana] += (float) $venta->total;
        }
        $carteraTotalHoy = Cliente::where('activo', 1)
            ->where('dia_cobro', $hoy->day)
            ->count();

        // Ya pagaron = tienen cobertura más allá de hoy (pagaron el período siguiente)
        $clientesAdelantados = Cliente::where('activo', 1)
            ->where('dia_cobro', $hoy->day)
            ->whereHas('ventas', function ($q) use ($hoy) {
                $q->where('periodo_fin', '>', $hoy);
            })
            ->count();

        $clientesVencidos = $carteraTotalHoy - $clientesAdelantados;
        $pagosHoyCount = Venta::whereDate('fecha_venta', $hoy)->count();

        $labelsPagos = ['Semana 1', 'Semana 2', 'Semana 3', 'Semana 4', 'Semana 5'];
        $dataPagos = [0, 0, 0, 0, 0];

        $ventasMes = Venta::whereMonth('fecha_venta', $ahora->month)
            ->whereYear('fecha_venta', $ahora->year)
            ->get(['fecha_venta']);

        foreach ($ventasMes as $venta) {
            $fecha = Carbon::parse($venta->fecha_venta);
            $indiceSemana = (int) floor(($fecha->day - 1) / 7);

            if ($indiceSemana > 4) {
                $indiceSemana = 4;
            }

            $dataPagos[$indiceSemana]++;
        }
        $clientesPorTipo = Cliente::select('tipo', DB::raw('COUNT(*) as total'))
            ->whereIn('tipo', ['A', 'B', 'C'])
            ->groupBy('tipo')
            ->pluck('total', 'tipo');

        $clientesPorTipo = collect([
            'A' => (int) ($clientesPorTipo['A'] ?? 0),
            'B' => (int) ($clientesPorTipo['B'] ?? 0),
            'C' => (int) ($clientesPorTipo['C'] ?? 0),
        ]);
        $estadoPagosData = $this->estadoPagosCliente($request);
        $clientesPagoAdelantado = Cliente::where('activo', 1)
            ->with([
                'ventas' => function ($q) use ($estadosPagados) {
                    $q->whereIn('estado', $estadosPagados)
                        ->orderByDesc('periodo_fin');
                }
            ])
            ->get()
            ->map(function ($cliente) use ($hoy) {
                $ultimaVenta = $cliente->ventas->first();

                // Solo considerar clientes con una venta pagada registrada
                if (!$ultimaVenta || !$ultimaVenta->periodo_fin) {
                    return null;
                }

                $mesesPagados = (int) ($ultimaVenta->meses ?? 1);

                // Solo clientes que pagaron 2 o más meses de adelanto
                if ($mesesPagados < 2) {
                    return null;
                }

                $proximoVencimiento = Carbon::parse($ultimaVenta->periodo_fin);
                $diasRestantes = $hoy->diffInDays($proximoVencimiento, false);

                // Si el periodo ya venció, no está "adelantado"
                if ($diasRestantes < 0) {
                    return null;
                }

                $rangoCubierto = '';
                if ($ultimaVenta->periodo_inicio && $ultimaVenta->periodo_fin) {
                    $rangoCubierto =
                        Carbon::parse($ultimaVenta->periodo_inicio)->translatedFormat('M') .
                        ' - ' .
                        Carbon::parse($ultimaVenta->periodo_fin)->translatedFormat('M');
                }

                return (object) [
                    'nombre' => $cliente->nombre,
                    'telefono' => $cliente->telefono1 ?? '-',
                    'tipo' => $cliente->tipo ?? '-',
                    'meses_pagados' => $mesesPagados,
                    'rango_cubierto' => $rangoCubierto,
                    'proximo_vencimiento' => $proximoVencimiento,
                    'monto_pagado' => $ultimaVenta->total,
                    'dias_restantes' => $diasRestantes,
                ];
            })
            ->filter()
            ->sortByDesc('dias_restantes')
            ->values();

        return view('ventas_clientes.index', [
            'totalCobradoDia' => $totalCobradoDia,
            'labelsCobrado' => $labelsCobrado,
            'dataCobrado' => $dataCobrado,
            'carteraTotalHoy' => $carteraTotalHoy,
            'clientesAdelantados' => $clientesAdelantados,
            'clientesVencidos' => $clientesVencidos,
            'pagosHoyCount' => $pagosHoyCount,
            'labelsPagos' => $labelsPagos,
            'dataPagos' => $dataPagos,
            'clientesPorTipo' => $clientesPorTipo,
            ...$estadoPagosData,
            'clientesPagoAdelantado' => $clientesPagoAdelantado,
        ]);
    }
    public function filtrarVentasClientes(Request $request)
    {
        $clientes = $this->clientesConEstado($request);
        return response()->json($clientes->values());
    }

    private function clientesConEstado(Request $request)
    {
        $hoy = Carbon::today();

        $query = Cliente::where('activo', 1)
            ->with([
                'ventas' => function ($q) {
                    $q->orderByDesc('fecha_venta');
                }
            ]);

        if ($request->filled('buscar')) {
            $query->where('nombre', 'like', '%' . $request->buscar . '%');
        }

        if ($request->filled('dia_pago')) {
            $query->whereHas('ventas', function ($q) use ($request) {
                $q->whereDay('periodo_fin', $request->dia_pago);
            });
        }

        $clientes = $query->get()->map(function ($cliente) use ($hoy) {
            $ultimaVenta = $cliente->ventas->first();

            $proximoVencimiento = $ultimaVenta?->periodo_fin;
            $dias = $proximoVencimiento
                ? $hoy->diffInDays(Carbon::parse($proximoVencimiento), false)
                : null;

            if (is_null($dias)) {
                $estado = 'SIN PAGOS';
                $badge = 'dark';
                $textoDias = 'Sin pagos registrados';
            } elseif ($dias < 0) {
                $estado = 'VENCIDO';
                $badge = 'danger';
                $textoDias = abs($dias) . ' días vencido';
            } elseif ($dias <= 7) {
                $estado = 'PRÓXIMO';
                $badge = 'warning';
                $textoDias = $dias . ' días';
            } else {
                $estado = 'AL CORRIENTE';
                $badge = 'success';
                $textoDias = $dias . ' días';
            }

            $historial = $cliente->ventas->take(10)->map(function ($venta) {
                return [
                    'venta_id' => $venta->id,
                    'fecha'    => optional($venta->fecha_venta)->locale('es')->translatedFormat('j F Y'),
                    'total'    => $venta->total,
                    'estado'   => $venta->estado,
                ];
            })->values();

            $totalPagado = $cliente->ventas
                ->whereIn('estado', ['cobrado', 'pagado'])
                ->sum('total');

            return (object) [
                'id' => $cliente->id,
                'nombre' => $cliente->nombre,
                'telefono' => $cliente->telefono1 ?? '-',
                'activo' => (bool) $cliente->activo,
                'dia_cobro' => $proximoVencimiento
                    ? Carbon::parse($proximoVencimiento)->day
                    : '-',
                'ultimo_pago' => optional($ultimaVenta?->fecha_venta)?->format('d/m/Y') ?? '-',
                'proximo_vencimiento' => $proximoVencimiento,
                'proximo_vencimiento_fmt' => $proximoVencimiento ? Carbon::parse($proximoVencimiento)->format('d/m/Y') : '-',
                'dias' => $dias,
                'estado' => $estado,
                'badge' => $badge,
                'texto_dias' => $textoDias,
                'historial' => $historial,
                'total_pagado' => $totalPagado,
            ];
        });

        if ($request->filled('dias_restantes')) {
            $filtro = $request->dias_restantes;

            $clientes = $clientes->filter(function ($cliente) use ($filtro) {
                if (is_null($cliente->dias)) {
                    return $filtro === 'vencidos';
                }

                return match ($filtro) {
                    'menos_7' => $cliente->dias >= 0 && $cliente->dias <= 7,
                    'vencidos' => $cliente->dias < 0,
                    'adelantados' => $cliente->dias > 7,
                    default => true,
                };
            });
        }

        return $clientes;
    }

    private function estadoPagosCliente(Request $request)
    {
        return [
            'clientes' => $this->clientesConEstado($request)->values(),
            'filtros' => $request->all(),
        ];
    }
}
