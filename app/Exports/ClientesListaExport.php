<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ClientesListaExport implements FromCollection, WithHeadings, WithTitle
{
    protected $clientes;

    public function __construct(Collection $clientes)
    {
        $this->clientes = $clientes;
    }

    public function collection()
    {
        $data = collect([
            ['Reporte de Clientes'],
            [],
        ]);

        $clientesData = $this->clientes->map(function ($cliente) {
            return [
                'ID' => $cliente->id,
                'Nombre' => $cliente->nombre ?? 'N/A',
                'Apellido' => $cliente->apellido ?? '-',
                'Teléfono' => $cliente->telefono ?? '-',
                'Dirección' => $cliente->direccion ?? '-',
            ];
        });

        return $data->merge($clientesData);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nombre',
            'Apellido',
            'Teléfono',
            'Dirección',
        ];
    }

    public function title(): string
    {
        return 'Reporte de Clientes';
    }
}