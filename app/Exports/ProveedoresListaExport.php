<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ProveedoresListaExport implements FromCollection, WithHeadings, WithTitle
{
    protected $proveedores;

    public function __construct(Collection $proveedores)
    {
        $this->proveedores = $proveedores;
    }

    public function collection()
    {
        $data = collect([
            ['Reporte de Proveedores'],
            [],
        ]);

        $proveedoresData = $this->proveedores->map(function ($proveedor) {
            return [
                'ID' => $proveedor->id,
                'Nombre' => $proveedor->nombre ?? 'N/A',
                'RUC' => $proveedor->ruc ?? '-',
                'Contacto' => $proveedor->contacto ?? '-',
                'Teléfono' => $proveedor->telefono ?? '-',
            ];
        });

        return $data->merge($proveedoresData);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nombre',
            'RUC',
            'Contacto',
            'Teléfono',
        ];
    }

    public function title(): string
    {
        return 'Reporte de Proveedores';
    }
}