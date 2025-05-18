<div>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Compras - Moto Repuesto Divino Niño</title>
    <link rel="stylesheet" href="{{ asset('css/compras.css') }}">
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
    @livewireStyles
</head>
<body>
<div class="container">
    <header class="main-header">
        <h1><i class='bx bx-purchase-tag'></i> Gestión de Compras</h1>
        <div class="header-actions">
            <button type="button" class="btn btn-primary" wire:click="openProveedorModal">
                <i class='bx bx-user-plus'></i> Nuevo Proveedor
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

    <main class="compra-form">
        <form>
            <div class="form-header">
                <h2><i class='bx bx-edit'></i> Nueva Compra</h2>
                <div class="form-group">
                    <label for="numeroCompra"><i class='bx bx-hash'></i> No. Compra</label>
                    <input type="text" id="numeroCompra" value="{{ $numero_compra }}" wire:model="numero_compra" readonly>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="search_proveedor"><i class='bx bx-user'></i> Proveedor *</label>
                    <input type="search"
                        id="search_proveedor"
                        wire:model="searchTermProveedor"
                        wire:key="proveedor-{{ now()->timestamp }}"
                        placeholder="Buscar proveedor..."
                        autocomplete="off"
                        list="proveedores-list"
                        class="form-control"
                        required
                        onkeypress="return /^[a-zA-Z0-9\s]+$/.test(event.key)">
                    <datalist id="proveedores-list">
                        @foreach($proveedoresFiltrados as $proveedor)
                            <option value="{{ $proveedor->nombre }}">{{ $proveedor->nombre }} ({{ $proveedor->razon_social ?? 'Sin razón social' }})</option>
                        @endforeach
                    </datalist>
                    @error('id_proveedor') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="fecha_compra"><i class='bx bx-calendar'></i> Fecha *</label>
                    <input type="date" id="fecha_compra" wire:model="fecha_compra" required readonly>
                    @error('fecha_compra') <span class="error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="form-grid form-grid-selection">
                <div class="form-group">
                    <label for="search_categoria"><i class='bx bx-category'></i> Categoría *</label>
                    <div class="search-input-container">
                    <input type="search"
                        id="search_categoria"
                        wire:model.lazy="searchTermCategoria"
                        wire:key="searchTermCategoria-{{ now()->timestamp }}"
                        placeholder="Buscar categoría..."
                        autocomplete="off"
                        list="categorias-list"
                        class="form-control"
                        required>
                        <datalist id="categorias-list">
                            @foreach($categoriasFiltradas as $categoria)
                                <option value="{{ $categoria->nombre }}">{{ $categoria->nombre }}</option>
                            @endforeach
                        </datalist>
                        <button type="button" class="btn btn-icon btn-primary btn-sm" wire:click="openCategoriaModal">
                            <i class='bx bx-plus'></i>
                        </button>
                    </div>
                    @error('categoria_id') <span class="error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="search_producto"><i class='bx bx-package'></i> Producto</label>
                    <div class="search-input-container">
                    <input type="search"
                        id="search_producto"
                        wire:model.lazy="searchTermProducto"
                        wire:key="searchTermProducto-{{ now()->timestamp }}"
                        placeholder="Buscar o seleccionar producto..."
                        autocomplete="off"
                        list="productos-list"
                        @if(empty($categoria_id)) disabled title="Seleccione una categoría primero" @endif
                        class="form-control">
                        <datalist id="productos-list">
                            @foreach($productosFiltrados as $producto)
                                <option value="{{ $producto->nombre }}">{{ $producto->nombre }} ({{ $producto->marca ?? 'Sin marca' }} / {{ $producto->modelo ?? 'Sin modelo' }} / {{ $producto->color ?? 'Sin color' }})</option>
                            @endforeach
                        </datalist>
                    </div>
                    @error('producto_id') <span class="error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group add-to-table">
                    <label class="invisible-label">Acción</label>
                    <button type="button" class="btn btn-blue" wire:click="agregarProductoExistente" @if(!$producto_id) disabled @endif>
                        <i class='bx bx-plus'></i> Agregar a la tabla
                    </button>
                </div>
            </div>

            <div class="productos-section">
                <div class="section-header">
                    <h3><i class='bx bx-package'></i> Productos</h3>
                    <button type="button" class="btn btn-secondary btn-sm" wire:click="agregarProductoNuevo">
                        <i class='bx bx-plus'></i> Agregar Producto Nuevo
                    </button>
                </div>

                <div class="table-container">
                    <table class="productos-table">
                        <thead>
                            <tr>
                                <th>Categoría *</th>
                                <th>Producto *</th>
                                <th>Marca *</th>
                                <th>Modelo *</th>
                                <th>Color *</th>
                                <th>Cantidad *</th>
                                <th>P. Compra *</th>
                                <th>P. Venta *</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($productos as $index => $producto)
                                <tr class="producto-row" wire:key="producto-item-{{ $index }}">
                                    <td>
                                        @if($producto['es_nuevo'])
                                            <select class="categoria-select" wire:model="productos.{{ $index }}.id_categoria" required>
                                                <option value="">Seleccionar Categoría</option>
                                                @foreach($categoriasFiltradas as $categoria)
                                                    <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                                                @endforeach
                                            </select>
                                            @error('productos.{{ $index }}.id_categoria') <span class="error">{{ $message }}</span> @enderror
                                        @else
                                            {{ $producto['categoria_nombre'] ?? 'Sin categoría' }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($producto['es_nuevo'])
                                            <input type="text" class="nombre-input" wire:model="productos.{{ $index }}.nombre" placeholder="Nombre" required
                                                   onkeypress="return /^[a-zA-Z0-9\s]+$/.test(event.key)"
                                                   oninput="this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, '')">
                                            @error('productos.{{ $index }}.nombre') <span class="error">{{ $message }}</span> @enderror
                                        @else
                                            {{ $producto['nombre'] ?? 'Sin nombre' }}
                                        @endif
                                    </td>
                                    <td>
                                        <input type="text" class="marca-input" wire:model="productos.{{ $index }}.marca" placeholder="Marca" required
                                               onkeypress="return /^[a-zA-Z0-9\s]+$/.test(event.key)"
                                               oninput="this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, '')">
                                        @error('productos.{{ $index }}.marca') <span class="error">{{ $message }}</span> @enderror
                                    </td>
                                    <td>
                                        <input type="text" class="modelo-input" wire:model="productos.{{ $index }}.modelo" placeholder="Modelo" {{ $producto['es_nuevo'] ? 'required' : 'readonly' }}
                                               onkeypress="return /^[a-zA-Z0-9\s]+$/.test(event.key)"
                                               oninput="this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, '')">
                                        @error('productos.{{ $index }}.modelo') <span class="error">{{ $message }}</span> @enderror
                                    </td>
                                    <td>
                                        <input type="text" class="color-input" wire:model="productos.{{ $index }}.color" placeholder="Color" {{ $producto['es_nuevo'] ? 'required' : 'readonly' }}
                                               onkeypress="return /^[a-zA-Z\s]+$/.test(event.key)"
                                               oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')">
                                        @error('productos.{{ $index }}.color') <span class="error">{{ $message }}</span> @enderror
                                    </td>
                                    <td>
                                        <input type="number" class="cantidad-input" wire:model="productos.{{ $index }}.cantidad" wire:change="calcularSubtotal({{ $index }})" min="1" required
                                               onkeydown="return !['e', 'E', '+', '-'].includes(event.key)"
                                               oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                        @error('productos.{{ $index }}.cantidad') <span class="error">{{ $message }}</span> @enderror
                                    </td>
                                    <td>
                                        <input type="number" class="precio-unitario-input" wire:model="productos.{{ $index }}.precio_unitario" wire:change="calcularSubtotal({{ $index }})" step="0.01" min="0.01" required
                                               onkeydown="return !['e', 'E', '+', '-'].includes(event.key)"
                                               oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1')">
                                        @error('productos.{{ $index }}.precio_unitario') <span class="error">{{ $message }}</span> @enderror
                                    </td>
                                    <td>
                                        <input type="number" class="precio-venta-input" wire:model="productos.{{ $index }}.precio_venta" step="0.01" min="0.01" required
                                               onkeydown="return !['e', 'E', '+', '-'].includes(event.key)"
                                               oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1')">
                                        @error('productos.{{ $index }}.precio_venta') <span class="error">{{ $message }}</span> @enderror
                                    </td>
                                    <td><span class="subtotal-display">{{ number_format($producto['subtotal'] ?? 0, 2) }}</span></td>
                                    <td>
                                        <button type="button" class="btn btn-icon btn-eliminar-producto" wire:click="eliminarProducto({{ $index }})">
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

            <div class="form-group">
                <label for="descripcion"><i class='bx bx-note'></i> Descripción</label>
                <textarea id="descripcion"
                    wire:model="descripcion"
                    wire:key="descripcion-{{ now()->timestamp }}"
                    rows="2"
                    placeholder="Detalles adicionales de la compra..."
                    onkeypress="return /^[a-zA-Z0-9\s]+$/.test(event.key)"
                    oninput="this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, '')"></textarea>
            </div>

            <div class="form-footer">
                <div class="total-display">
                    <span>Total:</span>
                    <span id="totalCompra" class="total-amount">{{ number_format($total, 2) }}</span>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary btn-lg" wire:click="limpiarCampos">
                        <i class='bx bx-reset'></i> Limpiar Campos
                    </button>
                    <button type="button" wire:click="guardarCompra" class="btn btn-primary btn-lg">
                        <i class='bx bx-check-circle'></i> Registrar Compra
                    </button>
                </div>
            </div>
        </form>
    </main>

    @if($showProveedorModal)
<div class="modal" style="display: flex;">
    <div class="modal-content">
        <span class="modal-close" wire:click="closeProveedorModal">×</span>
        <h2><i class='bx bx-user-plus'></i> Nuevo Proveedor</h2>
        <form wire:submit.prevent="guardarProveedor">
            <div class="form-grid">
                <div class="form-group">
                    <label for="modal_prov_nombre">Nombre *</label>
                    <input type="text" id="modal_prov_nombre" wire:model="nuevoProveedor.nombre" required
                           onkeypress="return /^[a-zA-Z.\s]+$/.test(event.key)"
                           oninput="this.value = this.value.replace(/[^a-zA-Z.\s]/g, '')">
                    @error('nuevoProveedor.nombre') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="modal_prov_razon">Razón Social *</label>
                    <input type="text" id="modal_prov_razon" wire:model="nuevoProveedor.razon_social" required
                           onkeypress="return /^[a-zA-Z.\s]+$/.test(event.key)"
                           oninput="this.value = this.value.replace(/[^a-zA-Z.\s]/g, '')">
                    @error('nuevoProveedor.razon_social') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="modal_prov_contacto">Persona de Contacto *</label>
                    <input type="text" id="modal_prov_contacto" wire:model="nuevoProveedor.contacto" required
                           onkeypress="return /^[a-zA-Z\s]+$/.test(event.key)"
                           oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')">
                    @error('nuevoProveedor.contacto') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="modal_prov_telefono">Teléfono *</label>
                    <input type="tel" id="modal_prov_telefono" wire:model="nuevoProveedor.telefono" required
                           onkeypress="return /^[0-9]+$/.test(event.key)"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8)">
                    @error('nuevoProveedor.telefono') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="modal_prov_email">Email *</label>
                    <input type="email" id="modal_prov_email" wire:model="nuevoProveedor.email" required>
                    @error('nuevoProveedor.email') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="modal_prov_ruc">RUC *</label>
                    <input type="text" id="modal_prov_ruc" wire:model="nuevoProveedor.ruc" required
                           onkeypress="return /^[0-9]+$/.test(event.key)"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 14)">
                    @error('nuevoProveedor.ruc') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label for="modal_prov_direccion">Dirección *</label>
                    <textarea id="modal_prov_direccion" wire:model="nuevoProveedor.direccion" rows="2" required
                              onkeypress="return /^[a-zA-Z0-9.,#\-_\s]+$/.test(event.key)"
                              oninput="this.value = this.value.replace(/[^a-zA-Z0-9.,#\-_\s]/g, '')"></textarea>
                    @error('nuevoProveedor.direccion') <span class="error">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class='bx bx-save'></i> Guardar Proveedor
                </button>
                <button type="button" class="btn btn-secondary" wire:click="closeProveedorModal">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>
@endif

    @if($showCategoriaModal)
    <div class="modal" style="display: flex;">
        <div class="modal-content">
            <span class="modal-close" wire:click="closeCategoriaModal">×</span>
            <h2><i class='bx bx-category'></i> Nueva Categoría</h2>
            <form wire:submit.prevent="guardarCategoria">
                <div class="form-group">
                    <label for="modal_cat_nombre">Nombre de la Categoría *</label>
                    <input type="text" id="modal_cat_nombre" wire:model="nuevaCategoria.nombre" required
                           onkeypress="return /^[a-zA-Z0-9\s]+$/.test(event.key)"
                           oninput="this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, '')">
                    @error('nuevaCategoria.nombre') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="modal_cat_descripcion">Descripción *</label>
                    <textarea id="modal_cat_descripcion" wire:model="nuevaCategoria.descripcion" rows="3" required
                              onkeypress="return /^[a-zA-Z0-9\s]+$/.test(event.key)"
                              oninput="this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, '')"></textarea>
                    @error('nuevaCategoria.descripcion') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class='bx bx-save'></i> Guardar Categoría
                    </button>
                    <button type="button" class="btn btn-secondary" wire:click="closeCategoriaModal">
                        Cancelar
                    </button>
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