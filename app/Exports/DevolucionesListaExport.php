<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class DevolucionesListaExport implements FromCollection, WithHeadings, WithTitle
{
    protected $devoluciones;

    public function __construct(Collection $devoluciones)
    {
        $this->devoluciones = $devoluciones;
    }

    public function collection()
    {
        $data = collect([
            ['Reporte de Devoluciones'],
            [],
        ]);

        $devolucionesData = $this->devoluciones->map(function ($devolucion) {
            return [
                'ID Devolución' => $devolucion->id,
                'Fecha' => $devolucion->fecha_devolucion ? \Carbon\Carbon::parse($devolucion->fecha_devolucion)->format('d/m/Y') : 'N/A',
                'Cliente' => $devolucion->factura && $devolucion->factura->cliente ? trim($devolucion->factura->cliente->nombre . ' ' . $devolucion->factura->cliente->apellido) : 'N/A',
                'Empleado' => $devolucion->empleado ? trim($devolucion->empleado->nombre . ' ' . $devolucion->empleado->apellido) : 'N/A',
                'Total' => 'C$ ' . number_format($devolucion->monto_total_devuelto, 2),
                'Motivo' => $devolucion->motivo_devolucion ?? '-',
            ];
        });

        return $data->merge($devolucionesData);
    }

    public function headings(): array
    {
        return [
            'ID Devolución',
            'Fecha',
            'Cliente',
            'Empleado',
            'Total',
            'Motivo',
        ];
    }

    public function title(): string
    {
        return 'Reporte de Devoluciones';
    }
}