<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Carbon\Carbon;
use Spatie\Activitylog\Models\Activity;

class Cliente extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'folio',
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
        'equiposregresados',
        'zona',
        'tipo',
        'documento',
    ];

    protected $casts = [
        'fecha_contrato' => 'date',
    ];

    public function getEstadoPagoActual()
    {
        $hoy = now()->startOfDay();
        $ultimaVenta = $this->ventas->sortByDesc('fecha_venta')->first();

        if (!$ultimaVenta) {
            return ['estado' => 'atrasado', 'mensaje' => 'Sin pagos registrados'];
        }

        $periodoInicio = Carbon::parse($ultimaVenta->periodo_inicio)->startOfMonth();
        $periodoFin = Carbon::parse($ultimaVenta->periodo_fin)->endOfMonth()->startOfDay();

        $inicio = ucfirst($periodoInicio->locale('es')->isoFormat('MMMM YYYY'));
        $fin = ucfirst($periodoFin->locale('es')->isoFormat('MMMM YYYY'));

        if ($periodoFin->lt($hoy->copy()->startOfMonth())) {
            return [
                'estado' => 'atrasado',
                'mensaje' => "Atrasado. Periodo cubierto: {$inicio} - {$fin}"
            ];
        }

        if ($periodoFin->gte($hoy->copy()->endOfMonth())) {
            return [
                'estado' => 'corriente',
                'mensaje' => "Al corriente. Periodo cubierto: {$inicio} - {$fin}"
            ];
        }

        $diaCobro = min($this->dia_cobro, $hoy->daysInMonth);

        $fechaCobro = Carbon::create(
            $hoy->year,
            $hoy->month,
            $diaCobro
        )->startOfDay();

        if ($hoy->lte($fechaCobro)) {
            $diasRestantes = $hoy->diffInDays($fechaCobro);

            if ($diasRestantes <= 5) {
                return [
                    'estado' => 'proximo',
                    'mensaje' => "Su pago es hoy Periodo cubierto: {$inicio} - {$fin}"
                ];
            }

            return [
                'estado' => 'corriente',
                'mensaje' => "Al corriente. Periodo cubierto: {$inicio} - {$fin}"
            ];
        }

        return [
            'estado' => 'atrasado',
            'mensaje' => "Atrasado. Periodo cubierto: {$inicio} - {$fin}"
        ];
    }


    public function getMesesAdeudados(): array
    {
        $hoy = now()->startOfDay();
        $ultimaVenta = $this->ventas->sortByDesc('fecha_venta')->first();

        if (!$ultimaVenta) {
            $inicio = $this->fecha_contrato
                ? Carbon::parse($this->fecha_contrato)->startOfMonth()
                : $hoy->copy()->startOfMonth();

            return $this->rangoDeMesesAdeudados($inicio, $hoy->copy()->startOfMonth());
        }

        $periodoFin = Carbon::parse($ultimaVenta->periodo_fin)->endOfMonth()->startOfDay();

        // El periodo cubierto termina antes del mes actual: se deben todos los meses
        // desde el mes siguiente al cubierto hasta el mes actual.
        if ($periodoFin->lt($hoy->copy()->startOfMonth())) {
            $inicio = $periodoFin->copy()->startOfMonth()->addMonthNoOverflow();
            return $this->rangoDeMesesAdeudados($inicio, $hoy->copy()->startOfMonth());
        }

        // El periodo cubierto llega hasta el mes actual o más allá: al corriente.
        if ($periodoFin->gte($hoy->copy()->endOfMonth())) {
            return [];
        }

        // El periodo cubierto termina dentro del mes actual: depende de si ya pasó
        // el día de cobro de este mes (misma regla que getEstadoPagoActual).
        $diaCobro = min($this->dia_cobro ?: 1, $hoy->daysInMonth);
        $fechaCobro = Carbon::create($hoy->year, $hoy->month, $diaCobro)->startOfDay();

        if ($hoy->lte($fechaCobro)) {
            return [];
        }

        return $this->rangoDeMesesAdeudados($hoy->copy()->startOfMonth(), $hoy->copy()->startOfMonth());
    }

    protected function rangoDeMesesAdeudados(Carbon $inicio, Carbon $fin): array
    {
        $meses = [];
        $cursor = $inicio->copy();

        while ($cursor->lte($fin)) {
            $meses[] = [
                'key' => $cursor->format('Y-m'),
                'label' => ucfirst($cursor->locale('es')->isoFormat('MMMM YYYY')),
            ];
            $cursor->addMonthNoOverflow();
        }

        return $meses;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('cliente')
            ->logOnly([
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
                'zona',
                'tipo',
                'documento',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->properties = $activity->properties->merge([
            'cliente_nombre' => $this->nombre,
        ]);
    }

    public function scopeOnlyBasicFields($query)
    {
        return $query->select('id', 'nombre', 'telefono1', 'fecha_contrato');
    }

    public function paquete()
    {
        return $this->belongsTo(Paquete::class);
    }

    public function equipos()
    {
        return $this->hasMany(Equipo::class);
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }

    public function ventaPendiente()
    {
        $ultimaVenta = $this->ventas()->latest()->first();
        return !$ultimaVenta || $ultimaVenta->estado == 'pendiente';
    }
     public function ultimaVentaPagada()
    {
        return $this->hasOne(Venta::class)
            ->where('estado', 'pagado')
            ->latestOfMany('periodo_fin');
    }
}
