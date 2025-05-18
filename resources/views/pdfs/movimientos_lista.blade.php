<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Listado de Movimientos de Inventario</title>
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
        .filters {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }
        .filters h2 {
            font-size: 16px;
            margin: 0 0 10px 0;
            color: #2d3748;
        }
        .filters ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .filters li {
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
        .table .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 10px;
            border-radius: 10px;
        }
        .table .badge-success {
            background-color: #c6f6d5;
            color: #22543d;
        }
        .table .badge-danger {
            background-color: #fed7e2;
            color: #742a2a;
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
            <h1>Listado de Movimientos de Inventario</h1>
            <p>Moto Repuesto Divino Niño</p>
            <p>Fecha: {{ now()->format('d/m/Y') }} | Contacto: info@motorepuesto.com</p>
        </div>

        <div class="filters">
            <h2>Filtros Aplicados</h2>
            <ul>
                <li><strong>Producto:</strong> {{ $filters['movimientosProducto'] }}</li>
                <li><strong>Tipo:</strong> {{ $filters['movimientosTipo'] }}</li>
                <li><strong>Fecha Inicio:</strong> {{ $filters['movimientosStartDate'] }}</li>
                <li><strong>Fecha Fin:</strong> {{ $filters['movimientosEndDate'] }}</li>
            </ul>
        </div>

        @if ($movimientos->isEmpty())
            <p style="text-align: center; color: #e53e3e;">No hay movimientos que coincidan con los filtros seleccionados.</p>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Producto</th>
                        <th>Tipo</th>
                        <th>Cantidad</th>
                        <th>Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($movimientos as $movimiento)
                        <tr>
                            <td>{{ $movimiento->id }}</td>
                            <td>{{ $movimiento->fecha ? \Carbon\Carbon::parse($movimiento->fecha)->format('d/m/Y H:i') : 'N/A' }}</td>
                            <td>
                                @if ($movimiento->producto)
                                    {{ implode(', ', array_filter([
                                        $movimiento->producto->nombre,
                                        $movimiento->producto->marca,
                                        $movimiento->producto->modelo
                                    ])) }}
                                @else
                                    Producto no disponible
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $movimiento->tipo === 'entrada' ? 'badge-success' : 'badge-danger' }}">
                                    {{ ucfirst($movimiento->tipo) }}
                                </span>
                            </td>
                            <td>{{ $movimiento->cantidad }}</td>
                            <td>{{ Str::limit($movimiento->descripcion, 50) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
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