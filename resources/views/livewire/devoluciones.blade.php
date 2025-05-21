<div>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devoluciones - Moto Repuesto Divino Niño</title>
    <link rel="stylesheet" href="{{ asset('css/devoluciones.css') }}">
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
    @livewireStyles
</head>
<body>
<div class="container">
    <header class="main-header">
        <h1><i class='bx bx-undo'></i> Devoluciones</h1>
    </header>

    @if (session()->has('message'))
        <div class="alert alert-success">
            <i class='bx bx-check-circle'></i> {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger">
            <i class='bx bx-error-circle'></i> {{ session('error') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li><i class='bx bx-error-circle'></i> {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <main class="devolucion-form">
        <div wire:loading wire:target="procesarDevolucion" class="loading-indicator">
            <i class='bx bx-loader-alt bx-spin'></i> Procesando devolución...
        </div>

        <div class="form-header">
            <h2><i class='bx bx-edit'></i> Nueva Devolución</h2>
            <div class="form-group">
                <label for="numero_devolucion"><i class='bx bx-hash'></i> No. Devolución</label>
                <input type="text" id="numero_devolucion" value="{{ $proximoDevolucionId }}" readonly class="form-control">
            </div>
        </div>

        <div class="form-grid-top">
            <div class="form-group">
                <label for="fecha_devolucion"><i class='bx bx-calendar'></i> Fecha</label>
                <input type="date" id="fecha_devolucion" wire:model="fechaDevolucion" readonly required class="form-control @error('fechaDevolucion') is-invalid @enderror">
                @error('fechaDevolucion') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="search_factura_id"><i class='bx bx-receipt'></i> ID Factura</label>
                <input type="number"
                       id="search_factura_id"
                       wire:model.lazy="searchTermFacturaId"
                       wire:key="search-factura-{{ now()->timestamp }}"
                       wire:keydown.enter="selectFacturaById"
                       wire:blur="selectFacturaById"
                       placeholder="ID de la factura..."
                       autofocus
                       class="form-control @error('factura_id') is-invalid @enderror"
                       onkeydown="return !['e', 'E', '+', '-'].includes(event.key)"
                       oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                @error('factura_id') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="search_cliente"><i class='bx bx-user'></i> Cliente</label>
                <input type="text"
                       id="search_cliente"
                       wire:model.defer="searchTermCliente"
                       wire:key="cliente-{{ now()->timestamp }}"
                       placeholder="Cliente de la factura..."
                       class="form-control"
                        readonly>
            </div>
        </div>

        <div class="productos-section">
            <div class="section-header">
                <h3><i class='bx bx-package'></i> Productos a Devolver</h3>
                <button type="button" class="btn btn-secondary btn-sm" wire:click="abrirModalProducto">
                    <i class='bx bx-plus'></i> Agregar Producto
                </button>
            </div>
            <div class="table-container">
                <table class="productos-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Color</th>
                            <th>Cant. Facturada</th>
                            <th>Cant. Devuelta</th>
                            <th>P. Unitario</th>
                            <th>IVA (%)</th>
                            <th>Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productosEnDevolucion as $index => $item)
                            <tr wire:key="producto-{{ $item['detalle_factura_id'] ?? 'manual-' . $index }}-{{ $index }}">
                                <td>{{ $item['nombre_producto'] }}</td>
                                <td>{{ $item['marca_producto'] }}</td>
                                <td>{{ $item['modelo_producto'] }}</td>
                                <td>{{ $item['color_producto'] }}</td>
                                <td>{{ $item['cantidad_facturada'] }}</td>
                                <td>
                                    <input type="number"
                                           min="1"
                                           max="{{ $item['cantidad_facturada'] }}"
                                           wire:model.lazy="productosEnDevolucion.{{ $index }}.cantidad_devuelta"
                                           class="input-table @error('productosEnDevolucion.{{ $index }}.cantidad_devuelta') is-invalid @enderror"
                                           onkeydown="return !['e', 'E', '+', '-'].includes(event.key)"
                                           oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                                    @error('productosEnDevolucion.{{ $index }}.cantidad_devuelta') <span class="error">{{ $message }}</span> @enderror
                                </td>
                                <td>{{ number_format($item['precio_unitario'], 2) }}</td>
                                <td>{{ number_format($item['iva'], 2) }}</td>
                                <td>C${{ number_format($item['subtotal_devuelto'], 2) }}</td>
                                <td>
                                    <button type="button" class="btn btn-icon btn-danger" wire:click="eliminarProducto({{ $index }})">
                                        <i class='bx bx-trash'></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">No hay productos agregados</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="form expert">
            <label for="motivo_devolucion"><i class='bx bx-comment'></i> Motivo</label>
            <textarea id="motivo_devolucion"
                      wire:model="motivoDevolucion"
                      wire:key="motivoDevolucion-{{ now()->timestamp }}"
                      placeholder="Motivo de la devolución..."
                      class="form-control @error('motivoDevolucion') is-invalid @enderror"
                      rows="4"
                      onkeypress="return /^[a-zA-Z0-9\s]+$/.test(event.key)"
                      oninput="this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, '')"></textarea>
            @error('motivoDevolucion') <span class="error">{{ $message }}</span> @enderror
        </div>
        <div class="form-footer">
            <div class="total-display">
                <div class="total-item">
                    <span>Subtotal:</span>
                    <span class="total-amount">C${{ number_format($subtotal, 2) }}</span>
                </div>
                <div class="total-item">
                    <span>IVA ({{ number_format($ivaResumen, 2) }}%):</span>
                    <span class="total-amount">C${{ number_format($ivaTotal, 2) }}</span>
                </div>
                <div class="total-item">
                    <span>Total Devuelto:</span>
                    <span class="total-amount">C${{ number_format($total, 2) }}</span>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-secondary btn-lg" wire:click="limpiarCampos">
                    <i class='bx bx-reset'></i> Limpiar Campos
                </button>
                <button type="button" class="btn btn-primary btn-lg" wire:click="procesarDevolucion" wire:loading.attr="disabled" wire:target="procesarDevolucion">
                    <span wire:loading wire:target="procesarDevolucion"><i class='bx bx-loader-alt bx-spin'></i> Procesando...</span>
                    <span wire:loading.remove wire:target="procesarDevolucion"><i class='bx bx-check-circle'></i> Procesar Devolución</span>
                </button>
            </div>
        </div>
    </main>

    @if($showModalProducto)
        <div class="modal show">
            <div class="modal-content">
                <span class="modal-close" wire:click="cerrarModalProducto">×</span>
                <h2><i class='bx bx-package'></i> Seleccionar Producto</h2>
                <form wire:submit.prevent="agregarProducto">
                    <div class="form-grid form-grid-selection">
                        <div class="form-group">
                            <label for="search_producto"><i class='bx bx-package'></i> Producto</label>
                            <div class="search-input-container">
                                <input type="search"
                                       id="search_producto"
                                       wire:model.lazy="searchTermProducto"
                                       wire:key="search-producto-{{ now()->timestamp }}"
                                       placeholder="Buscar o seleccionar producto..."
                                       autocomplete="off"
                                       list="productos-list"
                                       class="form-control @error('detalle_factura_id') is-invalid @enderror">
                                <datalist id="productos-list">
                                    @foreach($productosFiltrados as $detalle)
                                        <option value="{{ $detalle->producto->nombre }}" wire:click="selectProducto({{ $detalle->id }})">
                                            {{ $detalle->producto->nombre }} ({{ $detalle->producto->marca ?? 'Sin marca' }} / {{ $detalle->producto->modelo ?? 'Sin modelo' }} / {{ $detalle->producto->color ?? 'Sin color' }})
                                        </option>
                                    @endforeach
                                </datalist>
                            </div>
                            @error('detalle_factura_id') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="cantidad_devuelta"><i class='bx bx-box'></i> Cantidad a Devolver</label>
                            <input type="number"
                                   id="cantidad_devuelta"
                                   wire:model="cantidadDevuelta"
                                   min="1"
                                   max="{{ $maxCantidadDevolver }}"
                                   class="form-control @error('cantidadDevuelta') is-invalid @enderror"
                                   onkeydown="return !['e', 'E', '+', '-'].includes(event.key)"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                            @error('cantidadDevuelta') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group add-to-table">
                            <label class="invisible-label">Acción</label>
                            <button type="button" class="btn btn-blue" wire:click="agregarProducto" @if(!$detalle_factura_id) disabled @endif>
                                <i class='bx bx-plus'></i> Agregar a la tabla
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
@livewireScripts
</body>
</html>
</div>
