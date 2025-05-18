<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class VentasListaExport implements FromCollection, WithHeadings, WithTitle
{
    protected $ventas;

    public function __construct(Collection $ventas)
    {
        $this->ventas = $ventas;
    }

    public function collection()
    {
        // Agregar título como primera fila
        $data = collect([
            ['Reporte de Ventas'],
            [], // Fila vacía para separación
        ]);

        // Agregar datos
        $ventasData = $this->ventas->map(function ($venta) {
            return [
                'ID Factura' => $venta->id,
                'Fecha' => $venta->fecha_factura ? \Carbon\Carbon::parse($venta->fecha_factura)->format('d/m/Y H:i') : 'N/A',
                'Cliente' => trim(($venta->cliente->nombre ?? '') . ' ' . ($venta->cliente->apellido ?? '')) ?: 'N/A',
                'Empleado' => trim(($venta->empleado->nombre ?? '') . ' ' . ($venta->empleado->apellido ?? '')) ?: 'N/A',
                'IVA' => 'C$ ' . number_format($venta->iva ?? 0, 2), 
                'Total' => 'C$ ' . number_format($venta->total, 2),
                'Método Pago' => $venta->metodo_pago ?? 'N/A',
                'Total Cancelado' => 'C$ ' . number_format($venta->totalcancelado ?? 0, 2),
                'Cambio' => 'C$ ' . number_format($venta->cambio ?? 0, 2),
            ];
        });

        return $data->merge($ventasData);
    }

    public function headings(): array
    {
        return [
            'ID Factura',
            'Fecha',
            'Cliente',
            'Empleado',
            'IVA', 
            'Total',
            'Método Pago',
            'Total Cancelado',
            'Cambio',
        ];
    }

    public function title(): string
    {
        return 'Reporte de Ventas';
    }
}