<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class EmpleadosListaExport implements FromCollection, WithHeadings, WithTitle
{
    protected $empleados;

    public function __construct(Collection $empleados)
    {
        $this->empleados = $empleados;
    }

    public function collection()
    {
        $data = collect([
            ['Reporte de Empleados'],
            [],
        ]);

        $empleadosData = $this->empleados->map(function ($empleado) {
            return [
                'ID' => $empleado->id,
                'Nombre' => $empleado->nombre ?? 'N/A',
                'Apellido' => $empleado->apellido ?? '-',
                'Cargo' => $empleado->cargo ?? '-',
                'Teléfono' => $empleado->telefono ?? '-',
            ];
        });

        return $data->merge($empleadosData);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nombre',
            'Apellido',
            'Cargo',
            'Teléfono',
        ];
    }

    public function title(): string
    {
        return 'Reporte de Empleados';
    }
}