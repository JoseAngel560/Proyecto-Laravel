<div>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facturación - Moto Repuesto Divino Niño</title>
    <link rel="stylesheet" href="{{ asset('css/facturacion.css') }}">
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
    @livewireStyles
</head>
<body>
<div class="container">
    <header class="main-header">
        <h1><i class='bx bx-receipt'></i> Facturación</h1>
        <div class="header-actions">
            <button type="button" class="btn btn-primary btn-sm" wire:click="abrirModalNuevoCliente">
                <i class='bx bx-user-plus'></i> Nuevo Cliente
            </button>
        </div>
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

    <main class="factura-form">
        <div wire:loading wire:target="procesarFactura" class="loading-indicator">
            <i class='bx bx-loader-alt bx-spin'></i> Procesando factura...
        </div>

        <form>
            <div class="form-header">
                <h2><i class='bx bx-edit'></i> Nueva Factura</h2>
                <div class="form-group">
                    <label for="numero_factura"><i class='bx bx-hash'></i> No. Factura</label>
                    <input type="text" id="numero_factura" value="{{ $proximoFacturaId }}" readonly class="form-control">
                </div>
            </div>

            <div class="form-grid-top">
                <div class="form-group">
                    <label for="fecha"><i class='bx bx-calendar'></i> Fecha</label>
                    <input type="date" id="fecha" wire:model="fechaFactura" wire:key="fecha-{{ now()->timestamp }}" readonly required class="form-control @error('fechaFactura') is-invalid @enderror">
                    @error('fechaFactura') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="search_cliente"><i class='bx bx-user'></i> Cliente</label>
                    <div class="search-input-container">
                        <input type="search"
                               id="search_cliente"
                               wire:model.lazy="searchTermCliente"
                               wire:key="search-cliente-{{ now()->timestamp }}"
                               placeholder="Nombre, apellido o teléfono..."
                               autocomplete="off"
                               list="clientes-list"
                               class="form-control @error('cliente_id') is-invalid @enderror"
                               required>
                        <datalist id="clientes-list">
                            @foreach($clientesFiltrados as $cliente)
                                <option value="{{ $cliente->nombre }} {{ $cliente->apellido }}" wire:click="selectCliente({{ $cliente->id }})">
                                    {{ $cliente->nombre }} {{ $cliente->apellido }} ({{ $cliente->telefono ?? 'Sin teléfono' }})
                                </option>
                            @endforeach
                        </datalist>
                    </div>
                    @error('cliente_id') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="codigoProducto"><i class='bx bx-barcode'></i> Agregar Producto</label>
                    <input type="number"
                           id="codigoProducto"
                           wire:model="codigoProducto"
                           wire:key="codigo-producto-{{ now()->timestamp }}"
                           wire:keydown.enter="agregarPorCodigo"
                           placeholder="ID del producto..."
                           autofocus
                           class="form-control @error('codigoProducto') is-invalid @enderror"
                           onkeydown="return !['e', 'E', '+', '-'].includes(event.key)"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                    @error('codigoProducto') <span class="error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="productos-section">
                <div class="section-header">
                    <h3><i class='bx bx-package'></i> Productos</h3>
                    <button type="button" class="btn btn-secondary btn-sm" wire:click="abrirModalProductoManual">
                        <i class='bx bx-plus'></i> Agregar Producto Manualmente
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
                                <th>Stock</th>
                                <th>Cantidad</th>
                                <th>P. Unitario</th>
                                <th>IVA (%)</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($productosEnFactura as $index => $item)
                                <tr wire:key="producto-{{ $item['producto_id'] ?? 'manual-' . $index }}-{{ $index }}">
                                    <td>{{ $item['nombre_producto'] }}</td>
                                    <td>{{ $item['marca_producto'] }}</td>
                                    <td>{{ $item['modelo_producto'] }}</td>
                                    <td>{{ $item['color_producto'] }}</td>
                                    <td>{{ $item['stock'] }}</td>
                                    <td>
                                        <input type="number"
                                               min="1"
                                               wire:model.lazy="productosEnFactura.{{ $index }}.cantidad"
                                               wire:key="cantidad-{{ $index }}-{{ now()->timestamp }}"
                                               class="input-table @error('productosEnFactura.{{ $index }}.cantidad') is-invalid @enderror"
                                               onkeydown="return !['e', 'E', '+', '-'].includes(event.key)"
                                               oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                                        @error('productosEnFactura.{{ $index }}.cantidad') <span class="error">{{ $message }}</span> @enderror
                                    </td>
                                    <td>
                                        <input type="number"
                                               min="0"
                                               step="0.01"
                                               wire:model.lazy="productosEnFactura.{{ $index }}.precio_unitario"
                                               wire:key="precio-unitario-{{ $index }}-{{ now()->timestamp }}"
                                               class="input-table @error('productosEnFactura.{{ $index }}.precio_unitario') is-invalid @enderror"
                                               onkeydown="return !['e', 'E', '+', '-'].includes(event.key)"
                                               oninput="this.value = this.value.replace(/[^0-9.]/g, '');" required readonly >
                                        @error('productosEnFactura.{{ $index }}.precio_unitario') <span class="error">{{ $message }}</span> @enderror
                                    </td>
                                    <td>
                                        <input type="number"
                                               min="0"
                                               step="0.01"
                                               wire:model.lazy="productosEnFactura.{{ $index }}.iva"
                                               wire:key="iva-{{ $index }}-{{ now()->timestamp }}"
                                               class="input-table @error('productosEnFactura.{{ $index }}.iva') is-invalid @enderror"
                                               onkeydown="return !['e', 'E', '+', '-'].includes(event.key)"
                                               oninput="this.value = this.value.replace(/[^0-9.]/g, '');" required readonly>
                                        @error('productosEnFactura.{{ $index }}.iva') <span class="error">{{ $message }}</span> @enderror
                                    </td>
                                    <td>${{ number_format($item['subtotal'] ?? 0, 2) }}</td>
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

            <div class="form-grid">
                <div class="form-group">
                    <label for="metodo_pago"><i class='bx bx-credit-card'></i> Método de Pago</label>
                    <select id="metodo_pago" wire:model.lazy="metodo_pago" wire:key="metodo-pago-{{ now()->timestamp }}" class="form-control @error('metodo_pago') is-invalid @enderror">
                        <option value="Efectivo">Efectivo</option>
                        <option value="Tarjeta" @if($this->isTarjetaDisabled()) disabled @endif>Tarjeta</option>
                    </select>
                    @error('metodo_pago') <span class="error">{{ $message }}</span> @enderror
                </div>
                @if($metodo_pago == 'Efectivo')
                    <div class="form-group">
                        <label for="monto_pagado"><i class='bx bx-money'></i> Monto Pagado</label>
                        <input type="number"
                               id="monto_pagado"
                               wire:model.lazy="montoPagado"
                               wire:key="monto-pagado-{{ now()->timestamp }}"
                               placeholder="Monto recibido"
                               step="0.01"
                               min="0"
                               class="form-control @error('montoPagado') is-invalid @enderror"
                               onkeydown="return !['e', 'E', '+', '-'].includes(event.key)"
                               oninput="this.value = this.value.replace(/[^0-9.]/g, '');">
                        @error('montoPagado') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="cambio"><i class='bx bx-coin'></i> Cambio</label>
                        <input type="text" id="cambio" value="C${{ number_format($cambio, 2) }}" readonly class="form-control">
                    </div>
                @endif
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
                        <span>Total:</span>
                        <span class="total-amount">C${{ number_format($total, 2) }}</span>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary btn-lg" wire:click="limpiarCampos">
                        <i class='bx bx-reset'></i> Limpiar Campos
                    </button>
                    <button type="button" class="btn btn-primary btn-lg" wire:click="procesarFactura" wire:loading.attr="disabled" wire:target="procesarFactura">
                        <span wire:loading wire:target="procesarFactura"><i class='bx bx-loader-alt bx-spin'></i> Procesando...</span>
                        <span wire:loading.remove wire:target="procesarFactura"><i class='bx bx-check-circle'></i> Procesar</span>
                    </button>
                </div>
            </div>
        </form>
    </main>

    @if($showModalNuevoCliente)
        <div class="modal show">
            <div class="modal-content">
                <span class="modal-close" wire:click="cerrarModalNuevoCliente">×</span>
                <h2><i class='bx bx-user-plus'></i> Nuevo Cliente</h2>
                <form wire:submit.prevent="guardarNuevoCliente">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nuevo_cliente_nombre">Nombre*</label>
                            <input type="text"
                                   id="nuevo_cliente_nombre"
                                   wire:model="nuevoCliente.nombre"
                                   wire:key="nuevo-cliente-nombre-{{ now()->timestamp }}"
                                   required
                                   class="form-control @error('nuevoCliente.nombre') is-invalid @enderror"
                                   onkeypress="return /^[a-zA-Z\s]$/.test(event.key)"
                                   oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')">
                            @error('nuevoCliente.nombre') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="nuevo_cliente_apellido">Apellido*</label>
                            <input type="text"
                                   id="nuevo_cliente_apellido"
                                   wire:model="nuevoCliente.apellido"
                                   wire:key="nuevo-cliente-apellido-{{ now()->timestamp }}"
                                   required
                                   class="form-control @error('nuevoCliente.apellido') is-invalid @enderror"
                                   onkeypress="return /^[a-zA-Z\s]$/.test(event.key)"
                                   oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')">
                            @error('nuevoCliente.apellido') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="nuevo_cliente_telefono">Teléfono*</label>
                            <input type="tel"
                                   id="nuevo_cliente_telefono"
                                   wire:model="nuevoCliente.telefono"
                                   wire:key="nuevo-cliente-telefono-{{ now()->timestamp }}"
                                   required
                                   class="form-control @error('nuevoCliente.telefono') is-invalid @enderror"
                                   inputmode="numeric"
                                   minlength="8"
                                   maxlength="8"
                                   pattern="[0-9]{8}"
                                   title="Ingrese exactamente 8 dígitos numéricos"
                                   onkeypress="return (event.charCode !=8 && event.charCode ==0 || (event.charCode >= 48 && event.charCode <= 57))"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8);">
                            @error('nuevoCliente.telefono') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="nuevo_cliente_direccion">Dirección (Opcional)</label>
                            <textarea id="nuevo_cliente_direccion"
                                      wire:model="nuevoCliente.direccion"
                                      wire:key="nuevo-cliente-direccion-{{ now()->timestamp }}"
                                      rows="2"
                                      placeholder="Opcional"
                                      class="form-control @error('nuevoCliente.direccion') is-invalid @enderror"></textarea>
                            @error('nuevoCliente.direccion') <span class="error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" wire:click="cerrarModalNuevoCliente">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class='bx bx-save'></i> Guardar Cliente</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($showModalProductoManual)
        <div class="modal show">
            <div class="modal-content">
                <span class="modal-close" wire:click="cerrarModalProductoManual">×</span>
                <h2><i class='bx bx-package'></i> Agregar Producto Manualmente</h2>
                <form wire:submit.prevent="agregarProductoManual">
                    <div class="form-grid form-grid-selection">
                        <div class="form-group">
                            <label for="search_categoria"><i class='bx bx-category'></i> Categoría</label>
                            <div class="search-input-container">
                                <input type="search"
                                       id="search_categoria"
                                       wire:model.lazy="searchTermCategoria"
                                       wire:key="search-categoria-{{ now()->timestamp }}"
                                       placeholder="Buscar categoría..."
                                       autocomplete="off"
                                       list="categorias-list"
                                       class="form-control @error('categoria_id') is-invalid @enderror">
                                <datalist id="categorias-list">
                                    @foreach($categoriasFiltradas as $categoria)
                                        <option value="{{ $categoria->nombre }}" wire:click="selectCategoria({{ $categoria->id }})">{{ $categoria->nombre }}</option>
                                    @endforeach
                                </datalist>
                            </div>
                            @error('categoria_id') <span class="error">{{ $message }}</span> @enderror
                        </div>
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
                                       @if(empty($categoria_id)) disabled title="Seleccione una categoría primero" @endif
                                       class="form-control @error('producto_id') is-invalid @enderror">
                                <datalist id="productos-list">
                                    @foreach($productosFiltrados as $producto)
                                        <option value="{{ $producto->nombre }}" wire:click="selectProducto({{ $producto->id }})">{{ $producto->nombre }} ({{ $producto->marca ?? 'Sin marca' }} / {{ $producto->modelo ?? 'Sin modelo' }} / {{ $producto->color ?? 'Sin color' }})</option>
                                    @endforeach
                                </datalist>
                            </div>
                            @error('producto_id') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group add-to-table">
                            <label class="invisible-label">Acción</label>
                            <button type="button" class="btn btn-blue" wire:click="agregarProductoManual" @if(!$producto_id) disabled @endif>
                                <i class='bx bx-plus'></i> Agregar a la tabla
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($showModalTarjeta)
        <div class="modal show">
            <div class="modal-content">
                <span class="modal-close" wire:click="cerrarModalTarjeta">×</span>
                <h2><i class='bx bx-credit-card'></i> Pago con Tarjeta</h2>
                <form wire:submit.prevent="procesarFactura">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="tarjeta_nombre_titular">Nombre Titular</label>
                            <input type="text"
                                   id="tarjeta_nombre_titular"
                                   wire:model="tarjetaNombreTitular"
                                   wire:key="tarjeta-nombre-titular-{{ now()->timestamp }}"
                                   required
                                   class="form-control @error('tarjetaNombreTitular') is-invalid @enderror"
                                   onkeypress="return /^[a-zA-Z\s]+$/.test(event.key)"
                                   oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')">
                            @error('tarjetaNombreTitular') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="tarjeta_numero">Número Tarjeta</label>
                            <input type="text"
                                   id="tarjeta_numero"
                                   wire:model="tarjetaNumero"
                                   wire:key="tarjeta-numero-{{ now()->timestamp }}"
                                   placeholder="XXXX XXXX XXXX XXXX"
                                   required
                                   class="form-control @error('tarjetaNumero') is-invalid @enderror"
                                   inputmode="numeric"
                                   minlength="16"
                                   maxlength="16"
                                   pattern="[0-9]{16}"
                                   title="Ingrese exactamente 16 dígitos numéricos"
                                   onkeypress="return (event.charCode !=8 && event.charCode ==0 || (event.charCode >= 48 && event.charCode <= 57))"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 16);">
                            @error('tarjetaNumero') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="tarjeta_fecha_expiracion">Fecha Expiración</label>
                            <input type="month"
                                   id="tarjeta_fecha_expiracion"
                                   wire:model="tarjetaFechaExpiracion"
                                   wire:key="tarjeta-fecha-expiracion-{{ now()->timestamp }}"
                                   required
                                   class="form-control @error('tarjetaFechaExpiracion') is-invalid @enderror"
                                   min="{{ \Carbon\Carbon::now()->format('Y-m') }}">
                            @error('tarjetaFechaExpiracion') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="tarjeta_tipo">Tipo de Tarjeta</label>
                            <select id="tarjeta_tipo"
                                    wire:model="tarjetaTipo"
                                    wire:key="tarjeta-tipo-{{ now()->timestamp }}"
                                    class="form-control @error('tarjetaTipo') is-invalid @enderror">
                                <option value="Debito">Débito</option>
                                <option value="Credito">Crédito</option>
                            </select>
                            @error('tarjetaTipo') <span class="error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" wire:click="cerrarModalTarjeta">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class='bx bx-check-circle'></i> Confirmar Pago</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

 @if($showModalPdf)
    <div class="modal show" wire:keydown.enter="cerrarModalPdf">
        <div class="modal-content">
            <span class="modal-close" wire:click="cerrarModalPdf">×</span>
            <h2><i class='bx bx-file'></i> Vista Previa del Ticket</h2>
            @if(!empty($pdfErrorMessage))
                <div class="modal-error">{{ $pdfErrorMessage }} Contacte al soporte.</div>
            @elseif(empty($pdfBase64))
                <div class="modal-error">No se pudo generar el PDF. {{ $pdfErrorMessage ? $pdfErrorMessage : 'Verifique los datos e intente nuevamente.' }} Contacte al soporte.</div>
            @else
                <iframe class="pdf-viewer" :src="'data:application/pdf;base64,' + $wire.pdfBase64 + '#toolbar=0'" title="Ticket de Factura"></iframe>
            @endif
            <div class="modal-actions">
                <button class="btn btn-primary" wire:click="cerrarModalPdf" autofocus>Cerrar</button>
            </div>
        </div>
    </div>
@endif
</div>
@livewireScripts
</body>
</html>
</div>