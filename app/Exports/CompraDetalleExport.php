<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class CompraDetalleExport implements FromCollection, WithHeadings, WithTitle
{
    protected $detalles;

    public function __construct(Collection $detalles)
    {
        $this->detalles = $detalles;
    }

    public function collection()
    {
        $data = collect([
            ['Detalles de Compra'],
            [],
        ]);

        $detallesData = $this->detalles->map(function ($detalle) {
            return [
                'ID Compra' => $detalle->id_compra,
                'Producto' => $detalle->producto->id,
                'Nombre' => $detalle->producto->nombre ?? 'N/A',
                'Marca' => $detalle->producto->marca ?? '-',
                'Modelo' => $detalle->producto->modelo ?? '-',
                'Color' => $detalle->producto->color ?? '-',
                'Cantidad' => $detalle->cantidad,
                'Precio' => 'C$ ' . number_format($detalle->precio_unitario, 2),
                'Subtotal' => 'C$ ' . number_format($detalle->subtotal, 2),
            ];
        });

        return $data->merge($detallesData);
    }

    public function headings(): array
    {
        return [
            'ID Compra',
            'Producto',
            'Nombre',
            'Marca',
            'Modelo',
            'Color',
            'Cantidad',
            'Precio',
            'Subtotal',
        ];
    }

    public function title(): string
    {
        return 'Detalles de Compra';
    }
}