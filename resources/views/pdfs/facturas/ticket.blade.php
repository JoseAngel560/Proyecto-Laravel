<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ticket de Factura</title>
    <style>
        body {
            font-family: Helvetica, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 6px;
            width: 227px;
            box-sizing: border-box;
            line-height: 1.2;
        }
        h1 {
            font-size: 12px;
            text-align: center;
            margin: 0 0 6px 0;
            font-weight: bold;
        }
        .header, .footer {
            text-align: center;
            margin-bottom: 6px;
        }
        .info {
            margin-bottom: 6px;
        }
        .info p {
            margin: 1px 0;
            font-size: 9px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            table-layout: fixed;
        }
        th, td {
            padding: 3px;
            text-align: left;
            font-size: 9px;
            overflow: hidden;
            word-wrap: break-word;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        th:nth-child(1), td:nth-child(1) { 
            width: 48%; 
            line-height: 1.1; 
            max-height: 40px; 
        } /* Producto */
        th:nth-child(2), td:nth-child(2) { 
            width: 13%; 
            text-align: center; 
        } /* Cantidad */
        th:nth-child(3), td:nth-child(3) { 
            width: 13%; 
            text-align: right; 
        } /* Precio */
        th:nth-child(4), td:nth-child(4) { 
            width: 13%; 
            text-align: right; 
        } /* IVA */
        th:nth-child(5), td:nth-child(5) { 
            width: 13%; 
            text-align: right; 
        } /* Subtotal */
        .total {
            text-align: right;
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 6px;
        }
        .total p {
            margin: 1px 0;
        }
        .payment-details {
            text-align: right;
            font-size: 9px;
            margin-bottom: 6px;
        }
        .payment-details p {
            margin: 1px 0;
        }
        .footer p {
            font-size: 9px;
            margin: 1px 0;
        }
        .divider {
            border-top: 1px dashed #ccc;
            margin: 6px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Ticket de Factura</h1>
        <p>MotoRepuestos</p>
        <p>RUC: 123456789</p>
    </div>

    <div class="info">
        <p><strong>Factura:</strong> {{ $factura->proximoFacturaId ?? 'FAC' . str_pad($factura->id, 6, '0', STR_PAD_LEFT) }}</p>
        <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($factura->fecha_factura)->format('d/m/Y') }}</p>
        <p><strong>Cliente:</strong> {{ $cliente->nombre ?? '' }} {{ $cliente->apellido ?? '' }}</p>
        <p><strong>Teléfono:</strong> {{ $cliente->telefono ?? 'N/A' }}</p>
    </div>

    <div class="divider"></div>

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cant.</th>
                <th>Precio</th>
                <th>IVA</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($productos as $producto)
                <tr>
                    <td>
                        @php
                            $details = array_filter([
                                $producto['nombre_producto'] ?? 'N/A',
                                $producto['marca_producto'] ?? null,
                                $producto['modelo_producto'] ?? null,
                                $producto['color_producto'] ?? null
                            ]);
                            $productString = implode('/', $details);
                        @endphp
                        {{ $productString }}
                    </td>
                    <td>{{ $producto['cantidad'] ?? 0 }}</td>
                    <td>{{ number_format($producto['precio_unitario'] ?? 0, 2) }}</td>
                    <td>{{ number_format($producto['iva'] ?? 0, 2) }}%</td>
                    <td>{{ number_format($producto['subtotal'] ?? 0, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <div class="total">
        <p>Subtotal: {{ number_format($subtotal ?? 0, 2) }}</p>
        <p>IVA Total: {{ number_format($ivaTotal ?? 0, 2) }}</p>
        <p><strong>Total: {{ number_format(($subtotal ?? 0) + ($ivaTotal ?? 0), 2) }}</strong></p>
    </div>

    <div class="payment-details">
        <p><strong>Método de Pago:</strong> 
            @if($factura->metodo_pago == 'Tarjeta')
                {{ $factura->metodo_pago }} 
                @php
                    $datosTarjeta = \App\Models\DatosTarjeta::where('id_factura', $factura->id)->first();
                @endphp
                @if($datosTarjeta)
                    ({{ $datosTarjeta->tipo_tarjeta }}, ****{{ substr($datosTarjeta->numero_tarjeta, -4) }})
                @else
                    (N/A)
                @endif
            @else
                {{ $factura->metodo_pago }}
            @endif
        </p>
        <p><strong>Monto Cancelado:</strong> {{ number_format($factura->totalcancelado ?? 0, 2) }}</p>
        @if($factura->metodo_pago == 'Efectivo')
            <p><strong>Cambio:</strong> {{ number_format($factura->cambio ?? 0, 2) }}</p>
        @endif
    </div>

    <div class="divider"></div>

    <div class="footer">
        <p>Gracias por su compra</p>
        <p>MotoRepuestos - {{ \Carbon\Carbon::now()->format('Y') }}</p>
    </div>
</body>
</html>