<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class MovimientosListaExport implements FromCollection, WithHeadings, WithTitle
{
    protected $movimientos;

    public function __construct(Collection $movimientos)
    {
        $this->movimientos = $movimientos;
    }

    public function collection()
    {
        $data = collect([
            ['Reporte de Movimientos de Inventario'],
            [],
        ]);

        $movimientosData = $this->movimientos->map(function ($movimiento) {
            return [
                'ID' => $movimiento->id,
                'Fecha' => $movimiento->fecha ? \Carbon\Carbon::parse($movimiento->fecha)->format('d/m/Y H:i') : 'N/A',
                'Producto' => $movimiento->producto ? implode(', ', array_filter([
                    $movimiento->producto->nombre,
                    $movimiento->producto->marca,
                    $movimiento->producto->modelo
                ])) : 'Producto no disponible',
                'Tipo' => ucfirst($movimiento->tipo ?? 'N/A'),
                'Cantidad' => $movimiento->cantidad ?? '0',
                'Descripción' => $movimiento->descripcion ?? '-',
            ];
        });

        return $data->merge($movimientosData);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Fecha',
            'Producto',
            'Tipo',
            'Cantidad',
            'Descripción',
        ];
    }

    public function title(): string
    {
        return 'Reporte de Movimientos';
    }
}