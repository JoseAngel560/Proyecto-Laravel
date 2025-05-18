<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ProductosListaExport implements FromCollection, WithHeadings, WithTitle
{
    protected $productos;

    public function __construct(Collection $productos)
    {
        $this->productos = $productos;
    }

    public function collection()
    {
        $data = collect([
            ['Reporte de Productos'],
            [],
        ]);

        $productosData = $this->productos->map(function ($producto) {
            return [
                'ID' => $producto->id,
                'Nombre' => $producto->nombre ?? 'N/A',
                'Marca' => $producto->marca ?? '-',
                'Modelo' => $producto->modelo ?? '-',
                'Categoría' => $producto->categoria->nombre ?? 'N/A',
                'P. Venta' => 'C$ ' . number_format($producto->precio_venta, 2),
                'Stock' => $producto->stock ?? '0',
                'Estado' => $producto->estado ?? 'N/A',
            ];
        });

        return $data->merge($productosData);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nombre',
            'Marca',
            'Modelo',
            'Categoría',
            'P. Venta',
            'Stock',
            'Estado',
        ];
    }

    public function title(): string
    {
        return 'Reporte de Productos';
    }
}