<div>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Inventario - Moto Repuesto Divino Niño</title>
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="{{ asset('css/inventario.css') }}">
    @livewireStyles
</head>
<body>
<div class="container">
    <header class="main-header">
        <h1><i class='bx bx-package'></i> Gestión de Inventario</h1>
        <div class="header-actions">
            <button class="btn btn-primary" wire:click="openProductoModal">
                <i class='bx bx-plus'></i> Nuevo Producto
            </button>
            <button class="btn btn-secondary" wire:click="openMovimientoModal">
                <i class='bx bx-transfer'></i> Nuevo Movimiento
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

    <main class="inventario-container">
        <div class="tabs">
            <button class="tab-btn {{ $activeTab === 'productos' ? 'active' : '' }}" 
                    wire:click="setActiveTab('productos')">Productos</button>
            <button class="tab-btn {{ $activeTab === 'movimientos' ? 'active' : '' }}" 
                    wire:click="setActiveTab('movimientos')">Movimientos</button>
            <button class="tab-btn {{ $activeTab === 'categorias' ? 'active' : '' }}" 
                    wire:click="setActiveTab('categorias')">Categorías</button>
        </div>

        <div id="productos-tab" class="tab-content {{ $activeTab === 'productos' ? 'active' : '' }}">
            <div class="filtros-container">
                <div class="form-group">
                    <input type="text" wire:model.live="buscarProducto" placeholder="Buscar producto..."
                           onkeypress="return /^[a-zA-Z0-9\s]+$/.test(event.key)"
                           oninput="this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, '')">
                </div>
                <div class="form-group">
                    <select wire:model.live="filtroCategoria">
                        <option value="">Todas las categorías</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <input type="number" wire:model.live="filtroStockMaximo" placeholder="Buscar por stock ≤" min="0"
                           onkeydown="return !['e', 'E', '+', '-'].includes(event.key)"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </div>
            </div>

            <div class="table-container">
                <table class="inventario-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Color</th>
                            <th>Categoría</th>
                            <th>P. Venta</th>
                            <th>Stock</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productos as $producto)
                        <tr>
                            <td>{{ $producto->id }}</td>
                            <td>{{ $producto->nombre }}</td>
                            <td>{{ $producto->marca }}</td>
                            <td>{{ $producto->modelo }}</td>
                            <td>{{ $producto->color }}</td>
                            <td>{{ $producto->categoria->nombre }}</td>
                            <td>C$ {{ number_format($producto->precio_venta, 2) }}</td>
                            <td class="{{ $producto->stock <= $producto->stock_minimo ? 'text-danger' : '' }}">
                                {{ $producto->stock }}
                            </td>
                            <td>
                                <button class="btn btn-icon btn-primary btn-editar" 
                                        wire:click="editProducto({{ $producto->id }})">
                                    <i class='bx bx-edit'></i>
                                </button>
                                <button class="btn btn-icon btn-danger btn-eliminar" 
                                        wire:click="confirmDelete({{ $producto->id }}, 'producto')">
                                    <i class='bx bx-trash'></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center">No hay productos activos registrados</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pestaña de Movimientos -->
        <div id="movimientos-tab" class="tab-content {{ $activeTab === 'movimientos' ? 'active' : '' }}">
            <div class="filtros-container">
                <div class="form-group">
                    <select wire:model.live="filtroTipoMovimiento">
                        <option value="">Todos los movimientos</option>
                        <option value="entrada">Entradas</option>
                        <option value="salida">Salidas</option>
                    </select>
                </div>
                <div class="form-group">
                    <input type="date" wire:model.live="filtroFechaInicio" placeholder="Fecha inicio">
                </div>
                <div class="form-group">
                    <input type="date" wire:model.live="filtroFechaFin" placeholder="Fecha fin">
                </div>
                <div class="form-group">
                    <input type="text" wire:model.live="buscarMovimiento" placeholder="Buscar producto..."
                           onkeypress="return /^[a-zA-Z0-9\s]+$/.test(event.key)"
                           oninput="this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, '')">
                </div>
                <button class="btn btn-primary" wire:click="openMovimientoModal">
                    <i class='bx bx-plus'></i> Nuevo Movimiento
                </button>
            </div>

            <div class="table-container">
                <table class="inventario-table">
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
                        @forelse($movimientos as $movimiento)
                        <tr>
                            <td>{{ $movimiento->id }}</td>
                            <td>{{ date('d/m/Y H:i', strtotime($movimiento->fecha)) }}</td>
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
                                <span class="badge badge-{{ $movimiento->tipo === 'entrada' ? 'success' : 'danger' }}">
                                    {{ ucfirst($movimiento->tipo) }}
                                </span>
                            </td>
                            <td>{{ $movimiento->cantidad }}</td>
                            <td>{{ $movimiento->descripcion }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">No hay movimientos registrados</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div id="categorias-tab" class="tab-content {{ $activeTab === 'categorias' ? 'active' : '' }}">
            <div class="filtros-container">
                <div class="form-group">
                    <input type="text" wire:model.live="buscarCategoria" placeholder="Buscar categoría..."
                           onkeypress="return /^[a-zA-Z0-9\s]+$/.test(event.key)"
                           oninput="this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, '')">
                </div>
                <button class="btn btn-primary" wire:click="openCategoriaModal">
                    <i class='bx bx-plus'></i> Nueva Categoría
                </button>
            </div>

            <div class="table-container">
                <table class="inventario-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categorias as $categoria)
                        <tr>
                            <td>{{ $categoria->id }}</td>
                            <td>{{ $categoria->nombre }}</td>
                            <td>{{ $categoria->descripcion }}</td>
                            <td>
                                <button class="btn btn-icon btn-primary btn-editar" 
                                        wire:click="editCategoria({{ $categoria->id }})">
                                    <i class='bx bx-edit'></i>
                                </button>
                                <button class="btn btn-icon btn-danger btn-eliminar" 
                                        wire:click="confirmDelete({{ $categoria->id }}, 'categoria')">
                                    <i class='bx bx-trash'></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">No hay categorías registradas</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    @if($showProductoModal)
<div class="modal" style="display: flex;">
    <div class="modal-content">
        <span class="modal-close" wire:click="closeProductoModal">×</span>
        <h2><i class='bx bx-package'></i> {{ $isEditing ? 'Editar' : 'Nuevo' }} Producto</h2>
        <form wire:submit.prevent="saveProducto">
            <div class="form-grid">
                <div class="form-group">
                    <label for="nombre">Nombre *</label>
                    <input type="text" id="nombre" wire:model="nombre" required
                           onkeypress="return /^[a-zA-Z0-9\s]+$/.test(event.key)"
                           oninput="this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, '')">
                    @error('nombre') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="marca">Marca *</label>
                    <input type="text" id="marca" wire:model="marca" required
                           onkeypress="return /^[a-zA-Z0-9\s]+$/.test(event.key)"
                           oninput="this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, '')">
                    @error('marca') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="modelo">Modelo *</label>
                    <input type="text" id="modelo" wire:model="modelo" required
                           onkeypress="return /^[a-zA-Z0-9\s]+$/.test(event.key)"
                           oninput="this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, '')">
                    @error('modelo') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="color">Color *</label>
                    <input type="text" id="color" wire:model="color" required
                           onkeypress="return /^[a-zA-Z]+$/.test(event.key)"
                           oninput="this.value = this.value.replace(/[^a-zA-Z]/g, '')">
                    @error('color') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="categoria_id">Categoría *</label>
                    <select id="categoria_id" wire:model="categoria_id" required>
                        <option value="">Seleccionar categoría</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                        @endforeach
                    </select>
                    @error('categoria_id') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="precio_venta">Precio Venta *</label>
                    <input type="number" id="precio_venta" wire:model="precio_venta" step="0.01" min="0" required
                           onkeydown="return !['e', 'E', '+', '-'].includes(event.key)"
                           oninput="this.value = this.value.replace(/[^0-9.]/g, '')">
                    @error('precio_venta') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="stock">{{ $isEditing ? 'Stock Actual *' : 'Stock Inicial *' }}</label>
                    <input type="number" id="stock" wire:model="stock" required min="0" {{ $isEditing ? 'readonly' : '' }}
                           onkeydown="return !['e', 'E', '+', '-'].includes(event.key)"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    @if(!$isEditing)
                    @endif
                    @error('stock') <span class="error">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-cancelar" wire:click="closeProductoModal">Cancelar</button>
                <button type="submit" class="btn btn-primary">
                    <i class='bx bx-save'></i> Guardar Producto
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
        <h2><i class='bx bx-category'></i> {{ $isEditing ? 'Editar' : 'Nueva' }} Categoría</h2>
        <form wire:submit.prevent="saveCategoria">
            <div class="form-grid">
                <div class="form-group">
                    <label for="nombreCategoria">Nombre *</label>
                    <input type="text" id="nombreCategoria" wire:model="nombreCategoria" required
                           onkeypress="return /^[a-zA-Z]+$/.test(event.key)"
                           oninput="this.value = this.value.replace(/[^a-zA-Z]/g, '')">
                    @error('nombreCategoria') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="descripcionCategoria">Descripción *</label>
                    <textarea id="descripcionCategoria" wire:model="descripcionCategoria" rows="3" required
                              onkeypress="return /^[a-zA-Z0-9\s]+$/.test(event.key)"
                              oninput="this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, '')"></textarea>
                    @error('descripcionCategoria') <span class="error">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-cancelar" wire:click="closeCategoriaModal">Cancelar</button>
                <button type="submit" class="btn btn-primary">
                    <i class='bx bx-save'></i> Guardar Categoría
                </button>
            </div>
        </form>
    </div>
</div>
@endif

   @if($showMovimientoModal)
<div class="modal" style="display: flex;">
    <div class="modal-content">
        <span class="modal-close" wire:click="closeMovimientoModal">×</span>
        <h2><i class='bx bx-transfer'></i> Nuevo Movimiento</h2>
        <form wire:submit.prevent="saveMovimiento">
            <div class="form-grid">
                <div class="form-group">
                    <label for="tipo_movimiento">Tipo *</label>
                    <select id="tipo_movimiento" wire:model="tipo_movimiento" required>
                        <option value="">Seleccionar tipo</option>
                        <option value="entrada">Entrada</option>
                        <option value="salida">Salida</option>
                    </select>
                    @error('tipo_movimiento') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="search_categoria"><i class='bx bx-category'></i> Categoría *</label>
                    <div class="search-input-container">
                        <input type="search"
                               id="search_categoria"
                               wire:model.lazy="searchTermCategoria"
                               wire:key="search-categoria-{{ now()->timestamp }}"
                               placeholder="Buscar categoría..."
                               autocomplete="off"
                               list="categorias-list"
                               class="form-control @error('categoria_id') is-invalid @enderror"
                               required>
                        <datalist id="categorias-list">
                            @foreach($categoriasFiltradas as $categoria)
                                <option value="{{ $categoria->nombre }}" wire:click="selectCategoria({{ $categoria->id }})">{{ $categoria->nombre }}</option>
                            @endforeach
                        </datalist>
                    </div>
                    @error('categoria_id') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="search_producto"><i class='bx bx-package'></i> Producto *</label>
                    <div class="search-input-container">
                        <input type="search"
                               id="search_producto"
                               wire:model.lazy="searchTermProducto"
                               wire:key="search-producto-{{ now()->timestamp }}"
                               placeholder="Buscar o seleccionar producto..."
                               autocomplete="off"
                               list="productos-list"
                               @if(empty($categoria_id)) disabled title="Seleccione una categoría primero" @endif
                               class="form-control @error('producto_id') is-invalid @enderror"
                               required>
                        <datalist id="productos-list">
                            @foreach($productosFiltrados as $producto)
                                <option value="{{ $producto->nombre }}" wire:click="selectProducto({{ $producto->id }})">{{ $producto->nombre }} ({{ $producto->marca ?? 'Sin marca' }} / {{ $producto->modelo ?? 'Sin modelo' }} / {{ $producto->color ?? 'Sin color' }})</option>
                            @endforeach
                        </datalist>
                    </div>
                    @error('producto_id') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="cantidad">Cantidad *</label>
                    <input type="number" id="cantidad" wire:model="cantidad" required min="1"
                           onkeydown="return !['e', 'E', '+', '-'].includes(event.key)"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    @error('cantidad') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="fecha_movimiento">Fecha *</label>
                    <input type="datetime-local" id="fecha_movimiento" wire:model="fecha_movimiento" required readonly>
                    @error('fecha_movimiento') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group" style="grid-column: span 2">
                    <label for="descripcion_movimiento">Descripción *</label>
                    <textarea id="descripcion_movimiento" wire:model="descripcion_movimiento" rows="3" required
                              onkeypress="return /^[a-zA-Z0-9\s]+$/.test(event.key)"
                              oninput="this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, '')"></textarea>
                    @error('descripcion_movimiento') <span class="error">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-cancelar" wire:click="closeMovimientoModal">Cancelar</button>
                <button type="submit" class="btn btn-primary">
                    <i class='bx bx-save'></i> Registrar Movimiento
                </button>
            </div>
        </form>
    </div>
</div>
@endif

    @if($showConfirmModal)
    <div class="modal" style="display: flex;">
        <div class="modal-content" style="max-width: 400px;">
            <span class="modal-close" wire:click="closeConfirmModal">×</span>
            <h2><i class='bx bx-error-circle'></i> Confirmar Acción</h2>
            <p>
                ¿Está seguro de {{ $deleteType === 'producto' ? 'marcar como inactivo este producto' : 'eliminar esta categoría' }}?
                @if($deleteType === 'producto')
                    Esto evitará que el producto aparezca en la lista de productos activos.
                @endif
            </p>
            <div class="form-actions">
                <button type="button" class="btn btn-cancelar" wire:click="closeConfirmModal">Cancelar</button>
                <button type="button" class="btn btn-danger" wire:click="deleteConfirmed">
                    <i class='bx bx-trash'></i> {{ $deleteType === 'producto' ? 'Marcar como Inactivo' : 'Eliminar' }}
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
@livewireScripts
</body>
</html>
</div>

