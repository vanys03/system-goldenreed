<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venta;
use App\Models\Cliente;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = auth()->user();

        if ($user->can('Ver dashboard')) {
            $dashboardData = $this->getDashboardData($request);
            return view('dashboard.index', $dashboardData);
        }

        // Datos básicos para la vista limitada
        $clientesDeHoy = Cliente::where('dia_cobro', now()->day)
            ->where('activo', 1)
            ->get();

        $clientesAtrasados = Cliente::where('activo', 1)
            ->get()
            ->filter(function ($cliente) {
                return $cliente->getEstadoPagoActual()['estado'] === 'atrasado';
            });

        return view('dashboard.limitado', compact('clientesDeHoy', 'clientesAtrasados'));

    }

    protected function getDashboardData(Request $request): array
{
    $meses = [
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre'
    ];

    $clientesDeHoy = Cliente::where('dia_cobro', now()->day)
        ->where('activo', 1)
        ->get();

    $clientesAtrasados = Cliente::where('activo', 1)
        ->get()
        ->filter(function ($cliente) {
            return $cliente->getEstadoPagoActual()['estado'] === 'atrasado';
        });

    // Reemplazo del bloque problemático
    $ventasPorMes = [];
    for ($i = 1; $i <= 12; $i++) {
        $ventasPorMes[] = Venta::whereMonth('periodo_inicio', $i)
            ->whereYear('periodo_inicio', now()->year)
            ->sum('total');
    }

    return [
        'ventasMensuales' => $this->getVentasDelMesActual(),
        'ventasAnuales' => $this->getVentasAnuales(),
        'clientesTotales' => $this->getClientesTotales(),
        'clientesConDeuda' => $this->getClientesConDeuda(),
        'clientesPagados' => $this->getClientesPagados(),
        'clientesPendientesPago' => $this->getClientesPendientesDePago(),
        'ventasEfectivo' => $this->getVentasDelMesEfectivo(),
        'ventasTransferencia' => $this->getVentasDelMesTransferencia(),
        'mesActual' => now()->month,
        'meses' => $meses,
        'ventasPorMes' => $ventasPorMes,
        'clientesDeHoy' => $clientesDeHoy,
        'clientesAtrasados' => $clientesAtrasados,
    ];
}




    protected function getVentasDelMesActual(): float
    {
        return Venta::whereMonth('fecha_venta', now()->month)
            ->whereYear('fecha_venta', now()->year)
            ->sum('total');
    }
    
    protected function getVentasDelMesEfectivo(): float
    {
        return Venta::whereMonth('fecha_venta', now()->month)
            ->whereYear('fecha_venta', now()->year)
            ->where('tipo_pago', 'efectivo')
            ->sum('total');
    }
    
    protected function getVentasDelMesTransferencia(): float
    {
        return Venta::whereMonth('fecha_venta', now()->month)
            ->whereYear('fecha_venta', now()->year)
            ->where('tipo_pago', 'transferencia')
            ->sum('total');
    }

    protected function getVentasAnuales(): float
    {
        return Venta::whereYear('periodo_inicio', now()->year)
            ->sum('total');
    }

    protected function getClientesTotales(): int
    {
        return Cliente::count();
    }


    protected function getClientesConDeuda(): int
    {
        $hoy = now()->startOfDay();

        $clientesConVentasAtrasadas = DB::table('clientes as c')
            ->join(DB::raw("(SELECT cliente_id, MAX(periodo_fin) as max_fin 
                         FROM ventas 
                         GROUP BY cliente_id) as v_max"), 'c.id', '=', 'v_max.cliente_id')
            ->join('ventas as v', function ($join) {
                $join->on('v.cliente_id', '=', 'v_max.cliente_id')
                    ->on('v.periodo_fin', '=', 'v_max.max_fin');
            })
            ->where('c.activo', 1)
            ->where(function ($query) use ($hoy) {
                $query->where('v.estado', 'pendiente')
                    ->orWhere('v.periodo_fin', '<', $hoy);
            })
            ->pluck('c.id')
            ->toArray();
        $clientesSinVentas = Cliente::where('activo', 1) 
                ->doesntHave('ventas')
                ->get();
        $clientesSinVentasAtrasados = $clientesSinVentas->filter(function ($cliente) use ($hoy) {
$diaCobro = is_numeric($cliente->dia_cobro) ? (int) $cliente->dia_cobro : 1;
            $referencia = $hoy->copy()->day($diaCobro);
            if ($hoy->lt($referencia)) {
                $referencia->subMonthNoOverflow();
            }
            $primerDiaAtraso = $referencia->copy()->addDay();
            return $hoy->gte($primerDiaAtraso);
        })->pluck('id')->toArray();

        return count(array_unique(array_merge($clientesConVentasAtrasadas, $clientesSinVentasAtrasados)));
    }


    protected function getClientesPagados(): int
    {
        $hoy = now()->startOfDay();

        return DB::table('clientes as c')
            ->join(DB::raw("(SELECT cliente_id, MAX(periodo_fin) as max_fin 
                         FROM ventas 
                         GROUP BY cliente_id) as v_max"), 'c.id', '=', 'v_max.cliente_id')
            ->join('ventas as v', function ($join) {
                $join->on('v.cliente_id', '=', 'v_max.cliente_id')
                    ->on('v.periodo_fin', '=', 'v_max.max_fin');
            })
            ->where('v.estado', 'pagado')
            ->where('v.periodo_fin', '>=', $hoy)
            ->count();
    }


    protected function getClientesPendientesDePago(): int
    {
        $hoy = now()->startOfDay();
        $diaHoy = $hoy->day;

        // 1. Clientes sin ventas, pero hoy es su día de cobro
        $clientesSinVentas = Cliente::doesntHave('ventas')
            ->where('dia_cobro', $diaHoy)
            ->pluck('id')
            ->toArray();

        // 2. Clientes con ventas cuya última venta NO cubre hasta hoy y su día de cobro es hoy
        $clientesConVentas = DB::table('clientes as c')
            ->join(DB::raw("(SELECT cliente_id, MAX(periodo_fin) as max_fin 
                         FROM ventas 
                         GROUP BY cliente_id) as v_max"), 'c.id', '=', 'v_max.cliente_id')
            ->join('ventas as v', function ($join) {
                $join->on('v.cliente_id', '=', 'v_max.cliente_id')
                    ->on('v.periodo_fin', '=', 'v_max.max_fin');
            })
            ->where('c.dia_cobro', $diaHoy)
            ->where(function ($q) use ($hoy) {
                $q->where('v.periodo_fin', '<', $hoy)
                    ->orWhere('v.estado', 'pendiente');
            })
            ->pluck('c.id')
            ->toArray();

        return count(array_unique(array_merge($clientesSinVentas, $clientesConVentas)));
    }


}
