<?php

use App\Http\Controllers\ActividadController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\ClientesController;
use App\Http\Controllers\ClientesRentasController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\ExportarVentasController;
use App\Http\Controllers\PaquetesController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RentasController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\TelefonoController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\VentasController;
use App\Http\Controllers\{
    PlatformController,
    AccountController,
    AccountProfileController,
    ProfileAssignmentController
};
use App\Http\Middleware\NoCache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas públicas - Plataformas, cuentas y perfiles
|--------------------------------------------------------------------------
*/
Route::resource('platforms', PlatformController::class);
Route::resource('account-profiles', AccountProfileController::class);
Route::get('platforms/{platform}/accounts', [AccountController::class, 'byPlatform'])->name('platforms.accounts');
Route::get('accounts/{account}/profiles', [AccountProfileController::class, 'byAccount'])->name('accounts.profiles');
Route::post('/profiles/{profile}/assign', [AccountProfileController::class, 'assign'])->name('profiles.assign');
Route::post('/profiles/{profile}/unassign', [AccountProfileController::class, 'unassign'])->name('profiles.unassign');
Route::patch('/profiles/{profile}/pin', [AccountProfileController::class, 'updatePin']);

Route::get('/telefonos', [TelefonoController::class, 'index'])->name('telefonos.index');

/*
|--------------------------------------------------------------------------
| Rutas de prueba / utilitarias
|--------------------------------------------------------------------------
*/
Route::get('/test-carbon', function () {
    // ⚠️ Esto probablemente fallará si el servidor no reconoce "Julio"
    try {
        $fecha = Carbon::parse('1 Julio 2025');
        return 'Fecha interpretada correctamente: ' . $fecha->toDateString();
    } catch (\Exception $e) {
        return '⛔ Error: ' . $e->getMessage();
    }
});

Route::get('/', function () {
    return redirect(Auth::check() ? route('alt-dashboard') : route('login'));
});

/*
|--------------------------------------------------------------------------
| Rutas protegidas (auth + NoCache)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', NoCache::class])->group(function () {

    // ── Tickets ──────────────────────────────────────────────────────────
    Route::get('/tickets/perfil/{id}', [TicketController::class, 'perfil'])->name('ticket.perfil');
    Route::get('/ticket/perfil/{id}', [TicketController::class, 'reimprimirPerfil'])->name('ticket.perfil.reimprimir');

    // ── Plataformas y perfiles ───────────────────────────────────────────
    Route::get('/platforms/historial', [PlatformHistorialController::class, 'index'])->name('platforms_historial.index');
    Route::get('/platforms/historial/data', [PlatformHistorialController::class, 'data'])->name('historial.plataformas.data');
    Route::patch('/account-profiles/{profile}/unassign', [AccountProfileController::class, 'unassign'])->name('account-profiles.unassign');
    Route::get('/plataformas/historial', [ProfileAssignmentController::class, 'index'])->name('plataformas_historial.index');
    Route::get('/platforms-historial/data', [ProfileAssignmentController::class, 'data'])->name('platforms_historial.data');
    Route::get('/platforms/perfiles-ocupados/data', [AccountProfileController::class, 'dataPerfilesOcupados'])->name('platforms.occupied-profiles.data');

    // ── Rentas ───────────────────────────────────────────────────────────
    Route::patch('rentas/{renta}/confirmar', [RentasController::class, 'confirmar'])->name('rentas.confirmar');
    Route::patch('rentas/{renta}/cancelar', [RentasController::class, 'cancelar'])->name('rentas.cancelar');
    Route::get('/rentas/historial', [RentasController::class, 'historial'])->name('rentas_historial.index');
    Route::get('/clientes-rentas/buscar', [ClientesRentasController::class, 'buscar'])->name('clientes_rentas.buscar');
    Route::resource('rentas', RentasController::class);
    Route::get('tickets/rentas/{renta}/imprimible', [TicketController::class, 'imprimibleRenta'])->name('tickets.rentas.imprimible');
    Route::get('/rentas/historial/data', [RentasController::class, 'data'])->name('rentas_historial.data');
    Route::get('/rentas/{id}/detalle', [RentasController::class, 'detalle'])->name('rentas.detalle');
    Route::delete('/rentas/{id}', [RentasController::class, 'destroy'])->name('rentas.destroy');

    // ── Clientes de rentas ───────────────────────────────────────────────
    Route::resource('clientes-rentas', ClientesRentasController::class);
    Route::get('clientes/buscar', [ClientesRentasController::class, 'buscar'])->name('clientes.buscar');

    // ── Dashboard y perfil de usuario ────────────────────────────────────
    Route::get('/alt-dashboard', DashboardController::class)->name('alt-dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Usuarios, roles y catálogos ──────────────────────────────────────
    Route::resource('usuarios', UsuariosController::class);
    Route::resource('roles', RolesController::class);

    Route::get('clientes/{id}/edit-modal', [ClientesController::class, 'editModal'])->name('clientes.edit-modal');
    Route::get('clientes/{id}/delete-modal', [ClientesController::class, 'deleteModal'])->name('clientes.delete-modal');
    Route::resource('clientes', ClientesController::class);

    Route::resource('equipos', EquipoController::class);

    Route::get('/actividades-data', [ActividadController::class, 'data'])->name('actividades.data');
    Route::resource('actividades', ActividadController::class);

    Route::resource('paquetes', PaquetesController::class);

    Route::get('/documento/{carpeta}/{archivo}', function ($carpeta, $archivo) {
        $ruta = 'documentos/' . $carpeta . '/' . $archivo;

        if (!Storage::disk('public')->exists($ruta)) {
            abort(404);
        }

        return response()->file(
            storage_path('app/public/' . $ruta)
        );
    })->name('ver.documento');

    // ── Ventas, corte e historial ────────────────────────────────────────
    Route::get('/ventas/recargo/{cliente}', [VentasController::class, 'calcularRecargo']);
    Route::get('/ventas/buscar-clientes', [VentasController::class, 'buscarClientes'])->name('ventas.buscar-clientes');
    Route::get('/ventas/estado-cliente/{clienteId}', [VentasController::class, 'estadoCliente']);

    Route::resource('ventas', VentasController::class)->except(['show']);

    Route::get('/ventas/historial', [VentasController::class, 'historial'])->name('ventas.historial');
    Route::get('corte', [VentasController::class, 'corte'])->name('ventas.corte');

    Route::resource('auditoria', AuditoriaController::class);
    Route::put('/ventas/{venta}', [VentasController::class, 'update'])->name('ventas.update');

    Route::get('/ventas/exportar', [ExportarVentasController::class, 'exportar'])->name('ventas.exportar');

    Route::post('/ventas/pago-transferencia', [VentasController::class, 'pagarPorTransferencia'])->name('ventas.pago.transferencia');
    Route::get('/ventas/corte/cerrar', [VentasController::class, 'cerrarCorteHoy'])->name('ventas.corte.cerrar');
    Route::get('/ventas/corte/filtrar', [VentasController::class, 'filtrarCorte'])->name('ventas.corte.filtrar');

    Route::post('/clientes/{cliente}/deshabilitar', [ClientesController::class, 'deshabilitar'])->name('clientes.deshabilitar');

    // ── Cuentas ──────────────────────────────────────────────────────────
    Route::resource('accounts', AccountController::class);
    Route::post('/accounts/{account}/change-password', [AccountController::class, 'changePassword'])->name('accounts.changePassword');

});

/*
|--------------------------------------------------------------------------
| Clientes (público)
|--------------------------------------------------------------------------
*/
Route::get('/estado-clientes', [ClientesController::class, 'estadoClientes'])->name('estado_clientes.index');
Route::post('/clientes/{cliente}/toggle-estado', [ClientesController::class, 'toggleEstado']);
Route::post('/clientes/{cliente}/update-tipo', [ClientesController::class, 'updateTipo']);
Route::get('/reporte-clientes', [ClientesController::class, 'reporteClientes'])->name('reporte_clientes.index');

/*
|--------------------------------------------------------------------------
| Ventas de clientes (público)
|--------------------------------------------------------------------------
*/
Route::get('/ventas-clientes', [VentasController::class, 'ventasClientes'])->name('ventas_clientes.index');
Route::get('/ventas-clientes/filtrar', [VentasController::class, 'filtrarVentasClientes'])->name('ventas_clientes.filtrar');

/*
|--------------------------------------------------------------------------
| Tickets (público)
|--------------------------------------------------------------------------
*/
Route::get('/ticket/reimprimir/{id}', [TicketController::class, 'reimprimir'])->name('ticket.reimprimir');
Route::get('/ventas/{venta}/ticket-a/{cliente}', [TicketController::class, 'tipoA'])->name('tickets.tipo-a');
Route::get('/ventas/{venta}/ticket-b/{cliente}', [TicketController::class, 'tipoB'])->name('tickets.tipo-b');
Route::get('/ventas/{venta}/ticket-c/{cliente}', [TicketController::class, 'tipoC'])->name('tickets.tipo-c');
Route::get('/ventas/{venta}/ticket-generico/{cliente}', [TicketController::class, 'generico'])->name('tickets.generico');
Route::get('/tickets/reimprimir/{id}', [TicketController::class, 'reimprimir'])->name('tickets.reimprimir');

Route::get('/api/ventas/{venta}', function (App\Models\Venta $venta) {
    return response()->json([
        'cliente' => $venta->cliente->nombre,
        'paquete' => optional($venta->cliente->paquete)->nombre ?? 'N/A',
        'paquete_precio' => optional($venta->cliente->paquete)->precio ?? 0,
        'meses' => $venta->meses,
        'subtotal' => $venta->subtotal,
        'descuento' => $venta->descuento,
        'recargo_domicilio' => $venta->recargo_domicilio,
        'recargo_atraso' => $venta->recargo_atraso ?? 0,
        'total' => $venta->total,
        'estado' => $venta->estado,
        'tipo_pago' => $venta->tipo_pago,
        'fecha_venta' => optional($venta->fecha_venta)->format('d/m/Y'),
        'hora_venta' => optional($venta->created_at)->format('h:i A'),
        'periodo_inicio' => optional($venta->periodo_inicio)->format('d/m/Y'),
        'periodo_fin' => optional($venta->periodo_fin)->format('d/m/Y'),
        'registrado_por' => optional($venta->usuario)->name ?? 'N/A',
    ]);
});

Route::get('/ticket/imprimir/{venta}', [TicketController::class, 'imprimible'])->name('ticket.imprimible');
Route::get('/ventas/{id}/ticket', [TicketController::class, 'reimprimir'])->name('ventas.ticket');

require __DIR__ . '/auth.php';
