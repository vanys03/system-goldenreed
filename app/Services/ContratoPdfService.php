<?php

namespace App\Services;

use App\Models\Cliente;
use Carbon\Carbon;
use setasign\Fpdi\Fpdi;

class ContratoPdfService
{
    private const PLANTILLA = 'plantillas/contrato_servicio.pdf';

    private const COLOR = [0, 0, 0];
    private const FONT_SIZE = 9;

    public function generar(Cliente $cliente): string
    {
        $pdf = $this->crearDesdeTemplate();
        $totalPaginas = $pdf->setSourceFile(storage_path('app/' . self::PLANTILLA));

        $fecha = Carbon::parse($cliente->fecha_contrato);
        $mesInstalacion = ucfirst($fecha->locale('es')->isoFormat('MMMM'));

        $costoInstalacion = (float) config('contrato.costo_instalacion');
        $precioPaquete = (float) optional($cliente->paquete)->precio;
        $totalPagar = $costoInstalacion + $precioPaquete;

        $diaCobro = (int) $cliente->dia_cobro;
        $oportunoInicio = $diaCobro;
        $oportunoFin = $diaCobro + 2;
        $corte = $diaCobro + 3;
        $cancelacionInicio = $diaCobro + 3;
        $cancelacionFin = $diaCobro + 14;

        for ($pagina = 1; $pagina <= $totalPaginas; $pagina++) {
            $this->agregarPagina($pdf, $pagina);

            $this->escribirFolio($pdf, $cliente->folio);
            $this->put($pdf, 365, 118, $fecha->format('d'));
            $this->put($pdf, 395, 118, $mesInstalacion);
            $this->put($pdf, 435, 118, $fecha->format('Y'));

            $this->put($pdf, 50, 190, (string) $cliente->nombre);

            $this->put($pdf, 50, 225, (string) $cliente->telefono1);
            $this->put($pdf, 300, 225, (string) $cliente->telefono2);

            $this->put($pdf, 50, 265, (string) $cliente->direccion);
            $this->put($pdf, 300, 265, (string) $cliente->zona);

            $this->put($pdf, 130, 330, number_format($costoInstalacion, 2));

            $this->put($pdf, 170, 372, number_format($totalPagar, 2));
            $this->put($pdf, 360, 372, $mesInstalacion);

            $this->put($pdf, 50, 530, (string) $oportunoInicio);
            $this->put($pdf, 78, 530, (string) $oportunoFin);
            $this->put($pdf, 50, 560, (string) $corte);
            $this->put($pdf, 50, 595, (string) $cancelacionInicio);
            $this->put($pdf, 78, 595, (string) $cancelacionFin);
        }

        return $pdf->Output('S');
    }

    public function generarBlanco(int $siguienteFolioId): string
    {
        $pdf = $this->crearDesdeTemplate();
        $totalPaginas = $pdf->setSourceFile(storage_path('app/' . self::PLANTILLA));

        $costoInstalacion = (float) config('contrato.costo_instalacion');

        for ($pagina = 1; $pagina <= $totalPaginas; $pagina++) {
            $this->agregarPagina($pdf, $pagina);

            $this->escribirFolio($pdf, $siguienteFolioId);
            $this->put($pdf, 170, 370, number_format($costoInstalacion, 2));
        }

        return $pdf->Output('S');
    }

    private function crearDesdeTemplate(): Fpdi
    {
        return new Fpdi('P', 'pt', 'A4');
    }

    private function agregarPagina(Fpdi $pdf, int $numeroPagina): void
    {
        $pdf->AddPage();
        $tplId = $pdf->importPage($numeroPagina);
        $size = $pdf->getTemplateSize($tplId);
        $pdf->useTemplate($tplId, 0, 0, $size['width'], $size['height']);

        $pdf->SetTextColor(...self::COLOR);
        $pdf->SetFont('Helvetica', '', self::FONT_SIZE);
    }

    private function escribirFolio(Fpdi $pdf, ?int $folio): void
    {
        if ($folio === null) {
            return;
        }

        $this->put($pdf, 232, 118, sprintf('GD%04d', $folio));
    }

    private function put(Fpdi $pdf, float $x, float $y, string $text): void
    {
        $pdf->SetXY($x, $y - self::FONT_SIZE);
        $pdf->Cell(200, self::FONT_SIZE, $text, 0, 0);
    }
}
