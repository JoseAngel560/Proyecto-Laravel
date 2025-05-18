<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ComprasListaExport implements FromCollection, WithHeadings, WithTitle
{
    protected $compras;

    public function __construct(Collection $compras)
    {
        $this->compras = $compras;
    }

    public function collection()
    {
        $data = collect([
            ['Reporte de Compras'],
            [],
        ]);

        $comprasData = $this->compras->map(function ($compra) {
            return [
                'ID Compra' => $compra->id,
                'Fecha' => $compra->fecha_compra ? \Carbon\Carbon::parse($compra->fecha_compra)->format('d/m/Y') : 'N/A',
                'Proveedor' => $compra->proveedor->nombre ?? 'N/A',
                'Empleado' => trim(($compra->empleado->nombre ?? '') . ' ' . ($compra->empleado->apellido ?? '')) ?: 'N/A',
                'Total' => 'C$ ' . number_format($compra->total, 2),
                'Descripción' => $compra->descripcion ?? '-',
            ];
        });

        return $data->merge($comprasData);
    }

    public function headings(): array
    {
        return [
            'ID Compra',
            'Fecha',
            'Proveedor',
            'Empleado',
            'Total',
            'Descripción',
        ];
    }

    public function title(): string
    {
        return 'Reporte de Compras';
    }
}