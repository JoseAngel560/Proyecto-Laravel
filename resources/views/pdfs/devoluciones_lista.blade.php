<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Listado de Devoluciones</title>
    <style>
        @page {
            margin: 20mm 15mm;
            footer: html_pageFooter;
        }
        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #2d3748;
            line-height: 1.5;
        }
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            background-color: #2b6cb0;
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 28px;
            margin: 0;
            font-weight: bold;
        }
        .header p {
            margin: 5px 0;
            font-size: 11px;
            opacity: 0.9;
        }
        .filters, .summary {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }
        .filters h2, .summary h2 {
            font-size: 16px;
            margin: 0 0 10px 0;
            color: #2d3748;
        }
        .filters ul, .summary ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .filters li, .summary li {
            font-size: 12px;
            margin-bottom: 5px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .table th, .table td {
            border: 1px solid #e2e8f0;
            padding: 10px;
            text-align: left;
        }
        .table th {
            background-color: #edf2f7;
            font-weight: bold;
            color: #2d3748;
            text-transform: uppercase;
            font-size: 11px;
        }
        .table tbody tr:nth-child(even) {
            background-color: #f7fafc;
        }
        .table .text-right {
            text-align: right;
        }
        .table .badge {
            display: inline-block;
            padding: 2px 6px;
            background-color: #fefcbf;
            color: #744210;
            font-size: 10px;
            border-radius: 10px;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            color: #718096;
            margin-top: 20px;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Listado de Devoluciones</h1>
            <p>Moto Repuesto Divino Niño</p>
            <p>Fecha: {{ now()->format('d/m/Y') }} | Contacto: info@motorepuesto.com</p>
        </div>

        <div class="filters">
            <h2>Filtros Aplicados</h2>
            <ul>
                <li><strong>Tipo de Filtro:</strong> {{ $filters['devolucionesFilterType'] === 'date' ? 'Rango de Fechas' : 'ID Devolución' }}</li>
                <li><strong>Fecha Inicio:</strong> {{ $filters['devolucionesStartDate'] }}</li>
                <li><strong>Fecha Fin:</strong> {{ $filters['devolucionesEndDate'] }}</li>
            </ul>
        </div>

        @if ($devoluciones->isEmpty())
            <p style="text-align: center; color: #e53e3e;">No hay devoluciones que coincidan con los filtros seleccionados.</p>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>ID Devolución</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Empleado</th>
                        <th>Total</th>
                        <th>Motivo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($devoluciones as $devolucion)
                        <tr>
                            <td>{{ $devolucion->id }}</td>
                            <td>{{ $devolucion->fecha_devolucion ? \Carbon\Carbon::parse($devolucion->fecha_devolucion)->format('d/m/Y') : 'N/A' }}</td>
                            <td>
                                {{ $devolucion->factura && $devolucion->factura->cliente ? $devolucion->factura->cliente->nombre . ' ' . $devolucion->factura->cliente->apellido : 'N/A' }}
                                @if ($devolucion->factura && $devolucion->factura->cliente && $devolucion->factura->cliente->estado === 'inactivo')
                                    <span class="badge">Inactivo</span>
                                @endif
                            </td>
                            <td>{{ $devolucion->empleado ? ($devolucion->empleado->nombre . ' ' . $devolucion->empleado->apellido) : 'N/A' }}</td>
                            <td class="text-right">C$ {{ number_format($devolucion->monto_total_devuelto, 2) }}</td>
                            <td>{{ Str::limit($devolucion->motivo_devolucion, 50) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if ($filters['devolucionesFilterType'] === 'date')
                <div class="summary">
                    <h2>Resumen</h2>
                    <ul>
                        <li><strong>Total de Devoluciones:</strong> {{ $devoluciones->count() }}</li>
                        <li><strong>Total Devuelto:</strong> C$ {{ number_format($devoluciones->sum('monto_total_devuelto'), 2) }}</li>
                    </ul>
                </div>
            @endif
        @endif

        <div class="footer">
            Generado por Moto Repuesto Divino Niño | Todos los derechos reservados
        </div>
    </div>

    <htmlpagefooter name="pageFooter">
        <div style="text-align: right; font-size: 10px; color: #718096;">
            Página {PAGENO} de {nbpg}
        </div>
    </htmlpagefooter>
</body>
</html>