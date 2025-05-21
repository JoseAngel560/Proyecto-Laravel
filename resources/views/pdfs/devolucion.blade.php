<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Devolución</title>
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
        .section {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }
        .section h2 {
            font-size: 16px;
            margin: 0 0 10px 0;
            color: #2d3748;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 8px;
            border-bottom: 1px solid #e2e8f0;
        }
        .info-table td:first-child {
            font-weight: bold;
            width: 30%;
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
            <h1>Comprobante de Devolución</h1>
            <p>Moto Repuesto Divino Niño</p>
            <p>No. Devolución: DEV{{ str_pad($devolucion->id, 6, '0', STR_PAD_LEFT) }} | Fecha: {{ \Carbon\Carbon::parse($devolucion->fecha_devolucion)->format('d/m/Y') }}</p>
            <p>Contacto: info@motorepuesto.com</p>
        </div>

        <div class="section">
            <h2>Información del Cliente</h2>
            <table class="info-table">
                <tr><td>Nombre:</td><td>{{ ($cliente->nombre ?? 'N/A') . ' ' . ($cliente->apellido ?? '') }}</td></tr>
                <tr><td>Teléfono:</td><td>{{ $cliente->telefono ?? '-' }}</td></tr>
            </table>
        </div>

        <div class="section">
            <h2>Detalles de la Devolución</h2>
            <table class="info-table">
                <tr><td>No. Factura:</td><td>{{ $factura->id }}</td></tr>
                <tr><td>Fecha Factura:</td><td>{{ \Carbon\Carbon::parse($factura->fecha_factura)->format('d/m/Y') }}</td></tr>
                <tr><td>Motivo:</td><td>{{ $motivo ?? '-' }}</td></tr>
            </table>
        </div>

        <div class="section">
            <h2>Productos Devueltos</h2>
            @if (empty($productos) || count($productos) === 0)
                <p style="text-align: center; color: #e53e3e;">No hay productos registrados en esta devolución.</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Color</th>
                            <th>Cant. Devuelta</th>
                            <th>P. Unitario</th>
                            <th>IVA (%)</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($productos as $producto)
                            <tr>
                                <td>{{ $producto['nombre_producto'] ?? 'N/A' }}</td>
                                <td>{{ $producto['marca_producto'] ?? '-' }}</td>
                                <td>{{ $producto['modelo_producto'] ?? '-' }}</td>
                                <td>{{ $producto['color_producto'] ?? '-' }}</td>
                                <td>{{ $producto['cantidad_devuelta'] ?? 0 }}</td>
                                <td class="text-right">C$ {{ number_format($producto['precio_unitario'], 2) }}</td>
                                <td class="text-right">{{ number_format($producto['iva'], 2) }}</td>
                                <td class="text-right">C$ {{ number_format($producto['subtotal_devuelto'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="totals">
            <p>Subtotal: <span>C$ {{ number_format($subtotal, 2) }}</span></p>
            <p>IVA ({{ number_format($factura->iva ?? 0, 2) }}%): <span>C$ {{ number_format($ivaTotal, 2) }}</span></p>
            <p>Total Devuelto: <span>C$ {{ number_format($total, 2) }}</span></p>
        </div>

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