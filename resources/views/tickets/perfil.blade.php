<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Ticket Perfil</title>
 <style>
    @media print {
      @page { size: 48mm auto; margin: 0; }
      html, body {
        margin: 0; padding: 0; width: 100%;
      }
      body {
        font-family: 'Courier New', monospace;
        font-size: 14px; line-height: 1.4;
        text-align: left;
        padding-left: 12px; padding-right: 4px;
        word-break: break-word;
        overflow-wrap: break-word;
        white-space: normal;
      }
    }

    body {
      margin: 0; padding: 2px 12px;
      color: #000;
      font-family: 'Courier New', monospace;
      font-size: 14px; line-height: 1.4;
      text-align: left;
    }

    .bold { font-weight: bold; }

    .line {
      border-top: 1px dashed #999;
      margin: 6px 0;
    }
  </style>
</head>

<body onload="window.print();">

@php
    use Carbon\Carbon;
    $fechaInicio = Carbon::parse($assignment->started_at);
    $fechaFin = $fechaInicio->copy()->addMonth();
@endphp

<div class="bold">
    <img src="{{ asset('assets/img/logo1.png') }}" style="width: 150px;">
</div>

  <div>Calle Morelos 63</div>
  <div>Av. Reforma 34</div>

  <div class="line"></div>

  <div class="bold">Datos de Soporte</div>
  <div>227-201-97-71</div>
  <div>227-119-36-24</div>
  <div>Horario:</div>
  <div>Lunes - Sabado</div>
  <div>9:00 a.m - 6:00 p.m</div>
  <div>Domingo</div>
  <div>9:00 a.m - 3:00 p.m</div>

  <div class="bold">Depósitos o</div>
  <div class="bold">transferencias:</div>
  <div>Oxxo:</div>
  <div>4217 4700 6524 0505</div>
  <div>Bancomer:</div>
  <div>4152 3143 8636 6150</div>
  
<div class="line"></div>

<div class="bold">Datos del Perfil</div>
<div>Cliente: {{ $assignment->customer_name }}</div>
<div>Teléfono: {{ $assignment->telefono ?? '---' }}</div>

<div class="line"></div>

<div class="bold">Plataforma</div>
<div>{{ $assignment->profile->account->platform->name }}</div>

<div class="bold">Correo</div>
<div>{{ $assignment->profile->account->email }}</div>

<div class="bold">Contraseña</div>
<div>{{ $assignment->profile->account->password_plain ?? '---' }}</div>

<div class="bold">Perfil</div>
<div>{{ $assignment->profile->name }}</div>

<div class="bold">PIN</div>
<div>{{ $assignment->profile->notes ?? '---' }}</div>

<div class="line"></div>

<div class="bold">Datos del Ticket</div>
<div>ID Ticket: {{ $assignment->id }}</div>
<div>Desde: {{ $fechaInicio->format('Y-m-d') }}</div>
<div>Hasta: {{ $fechaFin->format('Y-m-d') }}</div>
<div>Vendedor: {{ $assignment->user->name ?? '---' }}</div>

<div class="line"></div>

<div style="font-size: 11px;">¡Gracias por tu preferencia!</div>

</body>
</html>
