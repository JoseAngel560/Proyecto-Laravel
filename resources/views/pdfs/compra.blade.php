<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Compra</title>
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
            <h1>Comprobante de Compra</h1>
            <p>Moto Repuesto Divino Niño</p>
            <p>No. Compra: CMP{{ str_pad($compra->id, 6, '0', STR_PAD_LEFT) }} | Fecha: {{ \Carbon\Carbon::parse($compra->fecha_compra)->format('d/m/Y') }}</p>
            <p>Contacto: info@motorepuesto.com</p>
        </div>

        <div class="section">
            <h2>Información del Proveedor</h2>
            <table class="info-table">
                <tr><td>Nombre:</td><td>{{ $proveedor->nombre ?? 'N/A' }}</td></tr>
                <tr><td>Razón Social:</td><td>{{ $proveedor->razon_social ?? '-' }}</td></tr>
                <tr><td>RUC:</td><td>{{ $proveedor->ruc ?? '-' }}</td></tr>
                <tr><td>Teléfono:</td><td>{{ $proveedor->telefono ?? '-' }}</td></tr>
                <tr><td>Dirección:</td><td>{{ $proveedor->direccion ?? '-' }}</td></tr>
            </table>
        </div>

        <div class="section">
            <h2>Detalles de la Compra</h2>
            <table class="info-table">
                <tr><td>Descripción:</td><td>{{ $compra->descripcion ?? '-' }}</td></tr>
                <tr><td>Total:</td><td>C$ {{ number_format($compra->total, 2) }}</td></tr>
            </table>
        </div>

        <div class="section">
            <h2>Productos</h2>
            @if (empty($productos) || count($productos) === 0)
                <p style="text-align: center; color: #e53e3e;">No hay productos registrados en esta compra.</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>Categoría</th>
                            <th>Nombre</th>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Color</th>
                            <th>Cantidad</th>
                            <th>P. Unitario</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($productos as $producto)
                            <tr>
                                <td>{{ $producto['categoria_nombre'] ?? \App\Models\Categoria::find($producto['id_categoria'])?->nombre ?? 'N/A' }}</td>
                                <td>{{ $producto['nombre'] ?? 'N/A' }}</td>
                                <td>{{ $producto['marca'] ?? '-' }}</td>
                                <td>{{ $producto['modelo'] ?? '-' }}</td>
                                <td>{{ $producto['color'] ?? '-' }}</td>
                                <td>{{ $producto['cantidad'] ?? 0 }}</td>
                                <td class="text-right">C$ {{ number_format($producto['precio_unitario'], 2) }}</td>
                                <td class="text-right">C$ {{ number_format($producto['subtotal'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
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