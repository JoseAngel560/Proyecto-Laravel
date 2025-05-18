<div>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Moto Repuesto Divino Niño</title>
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>  
    <link rel="stylesheet" href="{{ asset('css/reportes.css') }}">
    @livewireStyles
</head>
<body>
<div>
    <div class="container">
        <header class="main-header">
            <h1><i class='bx bxs-report'></i> Reportes Generales</h1>
            <div class="header-actions">
                <button type="button" class="btn btn-secondary" wire:click="limpiarFiltrosGenerales" title="Limpiar todos los filtros">
                    <i class='bx bx-reset'></i> Limpiar Filtros
                </button>
                <button type="button" class="btn btn-primary" wire:click="export('xlsx')">
                    <i class='bx bx-export'></i> Exportar Excel
                </button>
               <button type="button" class="btn btn-primary" wire:click="export('pdf')">
                    <i class='bx bx-export'></i> Exportar PDF
               </button>
            </div>
        </header>

        @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class='bx bx-check-circle'></i> {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class='bx bx-error-circle'></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session()->has('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class='bx bx-info-circle'></i> {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div wire:loading class="alert alert-info py-2 w-100 text-center">
            <i class='bx bx-loader-alt bx-spin'></i> Cargando datos...
        </div>

        <main class="inventario-container">
            <div class="tabs">
                <button wire:key="tab-ventas" class="tab-btn {{ $activeTab === 'ventas' ? 'active' : '' }}" wire:click="setActiveTab('ventas')">Ventas</button>
                <button wire:key="tab-compras" class="tab-btn {{ $activeTab === 'compras' ? 'active' : '' }}" wire:click="setActiveTab('compras')">Compras</button>
                <button wire:key="tab-productos" class="tab-btn {{ $activeTab === 'productos' ? 'active' : '' }}" wire:click="setActiveTab('productos')">Productos</button>
                <button wire:key="tab-proveedores" class="tab-btn {{ $activeTab === 'proveedores' ? 'active' : '' }}" wire:click="setActiveTab('proveedores')">Proveedores</button>
                <button wire:key="tab-clientes" class="tab-btn {{ $activeTab === 'clientes' ? 'active' : '' }}" wire:click="setActiveTab('clientes')">Clientes</button>
                <button wire:key="tab-empleados" class="tab-btn {{ $activeTab === 'empleados' ? 'active' : '' }}" wire:click="setActiveTab('empleados')">Empleados</button>
                <button wire:key="tab-movimientos" class="tab-btn {{ $activeTab === 'movimientos' ? 'active' : '' }}" wire:click="setActiveTab('movimientos')">Movimientos Inv.</button>
            </div>

            @if($activeTab === 'ventas')
            <div wire:key="content-ventas" id="ventas-tab" class="tab-content active">
                <div class="filtros-container">
                    <div class="form-group">
                        <label>Filtrar por:</label>
                        <select wire:model.live="ventasFilterType" wire:key="ventasFilterType-{{ now()->timestamp }}">
                            <option value="date">Rango de Fechas</option>
                            <option value="id">ID Factura</option>
                        </select>
                    </div>
                    @if($ventasFilterType === 'date')
                        <div class="form-group">
                            <label for="ventasStartDate">Fecha Inicio</label>
                            <input type="date" id="ventasStartDate" wire:model.live="ventasStartDate" wire:key="ventasStartDate-{{ now()->timestamp }}">
                        </div>
                        <div class="form-group">
                            <label for="ventasEndDate">Fecha Fin</label>
                            <input type="date" id="ventasEndDate" wire:model.live="ventasEndDate" wire:key="ventasEndDate-{{ now()->timestamp }}">
                        </div>
                    @else
                        <div class="form-group">
                            <label for="facturaId">ID Factura</label>
                            <input type="number" id="facturaId" wire:model.live="facturaId" placeholder="Ingrese ID"
                               onkeydown="return !['e', 'E', '+', '-'].includes(event.key)"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                    @error('codigoProducto') <span class="error">{{ $message }}</span> @enderror
                        </div>
                    @endif
                </div>

                <div class="table-container" wire:loading.remove wire:target="setActiveTab, ventasFilterType, ventasStartDate, ventasEndDate, facturaId">
                    @if(isset($ventas) && $ventas->count() > 0)
                    <table class="inventario-table">
                        <thead>
                            @if($ventasFilterType === 'date')
                            <tr>
                                <th>ID Factura</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Empleado</th>
                                <th>iva</th>
                                <th>Total</th>
                                <th>Método Pago</th>
                                <th>Total Cancelado</th>
                                <th>Cambio</th>
                            </tr>
                            @else
                            <tr>
                                <th>ID Factura</th>
                                <th>Producto</th>
                                <th>Nombre</th>
                                <th>Marca</th>
                                <th>Modelo</th>
                                <th>Color</th>
                                <th>Cantidad</th>
                                <th>Precio</th>
                                <th>IVA</th>
                                <th>Subtotal</th>
                            </tr>
                            @endif
                        </thead>
                        <tbody>
                            @if($ventasFilterType === 'date')
                                @foreach($ventas as $venta)
                                <tr wire:key="venta-{{ $venta->id }}">
                                    <td>{{ $venta->id }}</td>
                                    <td>{{ $venta->fecha_factura ? \Carbon\Carbon::parse($venta->fecha_factura)->format('d/m/Y H:i') : 'N/A' }}</td>
                                    <td>
                                        {{ $venta->cliente ? ($venta->cliente->nombre . ' ' . $venta->cliente->apellido) : 'N/A' }}
                                        @if($venta->cliente && $venta->cliente->estado === 'inactivo')
                                            <span class="badge rounded-pill bg-warning text-dark">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>{{ $venta->empleado ? ($venta->empleado->nombre . ' ' . $venta->empleado->apellido) : 'N/A' }}</td>
                                    <td>C$ {{ number_format($venta->iva, 2) }}</td>
                                    <td>C$ {{ number_format($venta->total, 2) }}</td>
                                    <td>{{ $venta->metodo_pago }}</td>
                                    <td>C$ {{ number_format($venta->totalcancelado ?? 0, 2) }}</td>
                                    <td>C$ {{ number_format($venta->cambio ?? 0, 2) }}</td>
                                </tr>
                                @endforeach
                            @else
                                @foreach($ventas as $venta)
                                    @foreach($venta->detalles as $detalle)
                                    <tr wire:key="venta-{{ $venta->id }}-detalle-{{ $detalle->id }}">
                                        <td>{{ $venta->id }}</td>
                                        <td>{{ $detalle->producto->id }}</td>
                                        <td>{{ $detalle->producto->nombre ?? 'N/A' }}</td>
                                        <td>{{ $detalle->producto->marca ?? '-' }}</td>
                                        <td>{{ $detalle->producto->modelo ?? '-' }}</td>
                                        <td>{{ $detalle->producto->color ?? '-' }}</td>
                                        <td>{{ $detalle->cantidad }}</td>
                                        <td>C$ {{ number_format($detalle->precio_unitario, 2) }}</td>
                                        <td>% {{ number_format($detalle->iva, 2) }}</td>
                                        <td>C$ {{ number_format($detalle->subtotal, 2) }}</td>
                                    </tr>
                                    @endforeach
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                    @elseif(isset($ventas))
                    <div class="alert alert-warning text-center">No hay ventas que coincidan con los filtros seleccionados.</div>
                    @endif
                </div>
            </div>
            @endif

            @if($activeTab === 'compras')
            <div wire:key="content-compras" id="compras-tab" class="tab-content active">
                <div class="filtros-container">
                    <div class="form-group">
                        <label>Filtrar por:</label>
                        <select wire:model.live="comprasFilterType" wire:key="comprasFilterType-{{ now()->timestamp }}">
                            <option value="date">Rango de Fechas</option>
                            <option value="id">ID Compra</option>
                        </select>
                    </div>
                    @if($comprasFilterType === 'date')
                        <div class="form-group">
                            <label for="comprasStartDate">Fecha Inicio</label>
                            <input type="date" id="comprasStartDate" wire:model.live="comprasStartDate" wire:key="comprasStartDate-{{ now()->timestamp }}">
                        </div>
                        <div class="form-group">
                            <label for="comprasEndDate">Fecha Fin</label>
                            <input type="date" id="comprasEndDate" wire:model.live="comprasEndDate" wire:key="comprasEndDate-{{ now()->timestamp }}">
                        </div>
                    @else
                        <div class="form-group">
                            <label for="compraId">ID Compra</label>
                            <input type="number" id="compraId" wire:model.live="compraId" placeholder="Ingrese ID"
                               onkeydown="return !['e', 'E', '+', '-'].includes(event.key)"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                        @error('codigoProducto') <span class="error">{{ $message }}</span> @enderror
                        </div>
                    @endif
                </div>

                <div class="table-container" wire:loading.remove wire:target="setActiveTab, comprasFilterType, comprasStartDate, comprasEndDate, compraId">
                    @if(isset($compras) && $compras->count() > 0)
                    <table class="inventario-table">
                        <thead>
                            @if($comprasFilterType === 'date')
                            <tr>
                                <th>ID Compra</th>
                                <th>Fecha</th>
                                <th>Proveedor</th>
                                <th>Empleado</th>
                                <th>Total</th>
                                <th>Descripción</th>
                            </tr>
                            @else
                            <tr>
                                <th>ID Compra</th>
                                <th>Producto</th>
                                <th>Nombre</th>
                                <th>Marca</th>
                                <th>Modelo</th>
                                <th>Color</th>
                                <th>Cantidad</th>
                                <th>Precio</th>
                                <th>Subtotal</th>
                            </tr>
                            @endif
                        </thead>
                        <tbody>
                            @if($comprasFilterType === 'date')
                                @foreach($compras as $compra)
                                <tr wire:key="compra-{{ $compra->id }}">
                                    <td>{{ $compra->id }}</td>
                                    <td>{{ $compra->fecha_compra ? \Carbon\Carbon::parse($compra->fecha_compra)->format('d/m/Y') : 'N/A' }}</td>
                                    <td>
                                        {{ $compra->proveedor ? $compra->proveedor->nombre : 'N/A' }}
                                        @if($compra->proveedor && $compra->proveedor->estado === 'inactivo')
                                            <span class="badge rounded-pill bg-warning text-dark">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>{{ $compra->empleado ? ($compra->empleado->nombre . ' ' . $compra->empleado->apellido) : 'N/A' }}</td>
                                    <td>C$ {{ number_format($compra->total, 2) }}</td>
                                    <td>{{ Str::limit($compra->descripcion, 50) }}</td>
                                </tr>
                                @endforeach
                            @else
                                @foreach($compras as $compra)
                                    @foreach($compra->detalles as $detalle)
                                    <tr wire:key="compra-{{ $compra->id }}-detalle-{{ $detalle->id }}">
                                        <td>{{ $compra->id }}</td>
                                        <td>{{ $detalle->producto->id }}</td>
                                        <td>{{ $detalle->producto->nombre ?? 'N/A' }}</td>
                                        <td>{{ $detalle->producto->marca ?? '-' }}</td>
                                        <td>{{ $detalle->producto->modelo ?? '-' }}</td>
                                        <td>{{ $detalle->producto->color ?? '-' }}</td>
                                        <td>{{ $detalle->cantidad }}</td>
                                        <td>C$ {{ number_format($detalle->precio_unitario, 2) }}</td>
                                        <td>C$ {{ number_format($detalle->subtotal, 2) }}</td>
                                    </tr>
                                    @endforeach
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                    @elseif(isset($compras))
                    <div class="alert alert-warning text-center">No hay compras que coincidan con los filtros seleccionados.</div>
                    @endif
                </div>
            </div>
            @endif

            @if($activeTab === 'productos')
            <div wire:key="content-productos" id="productos-tab" class="tab-content active">
                <div class="filtros-container">
                    <div class="form-group">
                        <label for="busquedaProd">Buscar</label>
                        <input type="text" id="busquedaProd" wire:model.lazy="busquedaProducto" placeholder="Nombre, marca, modelo..." wire:key="busquedaProducto-{{ now()->timestamp }}">
                    </div>
                    <div class="form-group">
                        <label for="productosCategoria">Categoría</label>
                        <select id="productosCategoria" wire:model.live="productosCategoria" wire:key="productosCategoria-{{ now()->timestamp }}">
                            <option value="todas">Todas</option>
                            @if(isset($categorias) && $categorias->count() > 0)
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="productosOrden">Ordenar por</label>
                        <select id="productosOrden" wire:model.live="productosOrden" wire:key="productosOrden-{{ now()->timestamp }}">
                            <option value="nombre_asc">Nombre (A-Z)</option>
                            <option value="nombre_desc">Nombre (Z-A)</option>
                            <option value="stock_asc">Stock (Menor a Mayor)</option>
                            <option value="stock_desc">Stock (Mayor a Menor)</option>
                        </select>
                    </div>
                </div>

                <div class="table-container" wire:loading.remove wire:target="setActiveTab, busquedaProducto, productosCategoria, productosOrden">
                    @if(isset($productos) && $productos->count() > 0)
                    <table class="inventario-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Marca</th>
                                <th>Modelo</th>
                                <th>Categoría</th>
                                <th>P. Venta</th>
                                <th>Stock</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($productos as $producto)
                            <tr wire:key="producto-{{ $producto->id }}">
                                <td>{{ $producto->id }}</td>
                                <td>{{ $producto->nombre }}</td>
                                <td>{{ $producto->marca }}</td>
                                <td>{{ $producto->modelo ?? '-' }}</td>
                                <td>{{ $producto->categoria->nombre ?? 'N/A' }}</td>
                                <td>C$ {{ number_format($producto->precio_venta, 2) }}</td>
                                <td>{{ $producto->stock ?? 'N/A' }}</td>
                                <td>{{ $producto->estado }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @elseif(isset($productos))
                    <div class="alert alert-warning text-center">No hay productos activos que coincidan con los filtros seleccionados.</div>
                    @endif
                </div>
            </div>
            @endif

            @if($activeTab === 'proveedores')
            <div wire:key="content-proveedores" id="proveedores-tab" class="tab-content active">
                <div class="filtros-container">
                    <div class="form-group" style="flex-grow: 1;">
                        <label for="busquedaProv">Buscar Proveedor</label>
                        <input type="text" id="busquedaProv" wire:model.lazy="busqueda" placeholder="Nombre, RUC, contacto..." wire:key="busqueda-{{ now()->timestamp }}">
                    </div>
                </div>

                <div class="table-container" wire:loading.remove wire:target="setActiveTab, busqueda">
                    @if(isset($proveedores) && $proveedores->count() > 0)
                    <table class="inventario-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>RUC</th>
                                <th>Contacto</th>
                                <th>Teléfono</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($proveedores as $proveedor)
                            <tr wire:key="proveedor-{{ $proveedor->id }}">
                                <td>{{ $proveedor->id }}</td>
                                <td>{{ $proveedor->nombre }}</td>
                                <td>{{ $proveedor->ruc ?? '-' }}</td>
                                <td>{{ $proveedor->contacto ?? '-' }}</td>
                                <td>{{ $proveedor->telefono ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @elseif(isset($proveedores))
                    <div class="alert alert-warning text-center">No hay proveedores que coincidan con la búsqueda.</div>
                    @endif
                </div>
            </div>
            @endif

            @if($activeTab === 'clientes')
            <div wire:key="content-clientes" id="clientes-tab" class="tab-content active">
                <div class="filtros-container">
                    <div class="form-group" style="flex-grow: 1;">
                        <label for="busquedaCli">Buscar Cliente</label>
                        <input type="text" id="busquedaCli" wire:model.lazy="busqueda" placeholder="Nombre, apellido, teléfono..." wire:key="busqueda-{{ now()->timestamp }}">
                    </div>
                </div>

                <div class="table-container" wire:loading.remove wire:target="setActiveTab, busqueda">
                    @if(isset($clientes) && $clientes->count() > 0)
                    <table class="inventario-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>Teléfono</th>
                                <th>Dirección</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($clientes as $cliente)
                            <tr wire:key="cliente-{{ $cliente->id }}">
                                <td>{{ $cliente->id }}</td>
                                <td>{{ $cliente->nombre }}</td>
                                <td>{{ $cliente->apellido }}</td>
                                <td>{{ $cliente->telefono ?? '-' }}</td>
                                <td>{{ Str::limit($cliente->direccion, 40) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @elseif(isset($clientes))
                    <div class="alert alert-warning text-center">No hay clientes que coincidan con la búsqueda.</div>
                    @else
                    <div wire:loading wire:target="setActiveTab('clientes')">Cargando clientes...</div>
                    @endif
                </div>
            </div>
            @endif

            @if($activeTab === 'empleados')
            <div wire:key="content-empleados" id="empleados-tab" class="tab-content active">
                <div class="filtros-container">
                    <div class="form-group" style="flex-grow: 1;">
                        <label for="busquedaEmp">Buscar Empleado</label>
                        <input type="text" id="busquedaEmp" wire:model.lazy="busqueda" placeholder="Nombre, apellido, cédula, cargo..." wire:key="busqueda-{{ now()->timestamp }}">
                    </div>
                </div>

                <div class="table-container" wire:loading.remove wire:target="setActiveTab, busqueda">
                    @if(isset($empleados) && $empleados->count() > 0)
                    <table class="inventario-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>Cargo</th>
                                <th>Teléfono</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($empleados as $empleado)
                            <tr wire:key="empleado-{{ $empleado->id }}">
                                <td>{{ $empleado->id }}</td>
                                <td>{{ $empleado->nombre }}</td>
                                <td>{{ $empleado->apellido }}</td>
                                <td>{{ $empleado->cargo ?? '-' }}</td>
                                <td>{{ $empleado->telefono ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @elseif(isset($empleados))
                    <div class="alert alert-warning text-center">No hay empleados que coincidan con la búsqueda.</div>
                    @else
                    <div wire:loading wire:target="setActiveTab('empleados')">Cargando empleados...</div>
                    @endif
                </div>
            </div>
            @endif

            @if($activeTab === 'movimientos')
            <div wire:key="content-movimientos" id="movimientos-tab" class="tab-content active">
                <div class="filtros-container">
                    <div class="form-group">
                        <label for="movimientosProducto">Producto</label>
                        <input type="text" id="movimientosProducto" wire:model.lazy="movimientosProducto" placeholder="Buscar por nombre..." wire:key="movimientosProducto-{{ now()->timestamp }}">
                    </div>
                    <div class="form-group">
                        <label for="movimientosTipo">Tipo</label>
                        <select id="movimientosTipo" wire:model.live="movimientosTipo" wire:key="movimientosTipo-{{ now()->timestamp }}">
                            <option value="">Todos</option>
                            <option value="entrada">Entrada</option>
                            <option value="salida">Salida</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="movimientosStartDate">Fecha Inicio</label>
                        <input type="date" id="movimientosStartDate" wire:model.live="movimientosStartDate" wire:key="movimientosStartDate-{{ now()->timestamp }}">
                    </div>
                    <div class="form-group">
                        <label for="movimientosEndDate">Fecha Fin</label>
                        <input type="date" id="movimientosEndDate" wire:model.live="movimientosEndDate" wire:key="movimientosEndDate-{{ now()->timestamp }}">
                    </div>
                </div>

                <div class="table-container" wire:loading.remove wire:target="setActiveTab, movimientosProducto, movimientosTipo, movimientosStartDate, movimientosEndDate">
                    @if(isset($movimientos) && $movimientos->count() > 0)
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
                            @foreach($movimientos as $movimiento)
                            <tr wire:key="movimiento-{{ $movimiento->id }}">
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
                                    <span class="badge rounded-pill bg-{{ $movimiento->tipo === 'entrada' ? 'success' : 'danger' }}">
                                        {{ ucfirst($movimiento->tipo) }}
                                    </span>
                                </td>
                                <td>{{ $movimiento->cantidad }}</td>
                                <td>{{ Str::limit($movimiento->descripcion, 50) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @elseif(isset($movimientos))
                    <div class="alert alert-warning text-center">No hay movimientos que coincidan con los filtros seleccionados.</div>
                    @endif
                </div>
            </div>
            @endif

            <div wire:ignore.self class="modal" style="{{ $showDetailsModal ? 'display: flex;' : 'display: none;' }}" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="{{ !$showDetailsModal }}">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
                    <div class="modal-content">
                        @if($showDetailsModal && $detailsItem)
                        <div class="modal-header">
                            <h5 class="modal-title" id="detailsModalLabel"><i class='bx bx-show'></i> Detalles de {{ ucfirst($detailsType) }}</h5>
                            <button type="button" class="btn-close" wire:click="closeDetailsModal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="details-grid row">
                                @if($detailsType === 'venta')
                                    <div class="col-md-6">
                                        <h4>Información Factura</h4>
                                        <p><strong>ID Factura:</strong> {{ $detailsItem->id }}</p>
                                        <p><strong>Fecha:</strong> {{ $detailsItem->fecha_factura ? \Carbon\Carbon::parse($detailsItem->fecha_factura)->format('d/m/Y H:i') : 'N/A' }}</p>
                                        <p>
                                            <strong>Cliente:</strong> 
                                            {{ $detailsItem->cliente ? ($detailsItem->cliente->nombre . ' ' . $detailsItem->cliente->apellido) : 'N/A' }}
                                            @if($detailsItem->cliente && $detailsItem->cliente->estado === 'inactivo')
                                                <span class="badge rounded-pill bg-warning text-dark">Inactivo</span>
                                            @endif
                                        </p>
                                        <p><strong>Empleado:</strong> {{ $detailsItem->empleado ? ($detailsItem->empleado->nombre . ' ' . $detailsItem->empleado->apellido) : 'N/A' }}</p>
                                        <p><strong>Nº Factura:</strong> {{ $detailsItem->numero_factura ?? '-' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h4>Detalles Pago</h4>
                                        <p><strong>Método Pago:</strong> {{ $detailsItem->metodo_pago }}</p>
                                        <p><strong>Total:</strong> C$ {{ number_format($detailsItem->total, 2) }}</p>
                                        @if($detailsItem->metodo_pago === 'Efectivo')
                                            <p><strong>Total Cancelado:</strong> C$ {{ number_format($detailsItem->totalcancelado, 2) }}</p>
                                            <p><strong>Cambio:</strong> C$ {{ number_format($detailsItem->cambio, 2) }}</p>
                                        @endif
                                    </div>
                                    <div class="col-12 mt-3">
                                        <h4>Productos Vendidos</h4>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead>
                                                    <tr><th>Producto</th><th>Cant.</th><th>P. Unit.</th><th>IVA</th><th>Subtotal</th></tr>
                                                </thead>
                                                <tbody>
                                                @forelse($detailsItem->detalles as $detalle)
                                                    <tr>
                                                        <td>
                                                            @if ($detalle->producto)
                                                                {{ implode(', ', array_filter([
                                                                    $detalle->producto->nombre,
                                                                    $detalle->producto->marca,
                                                                    $detalle->producto->modelo
                                                                ])) }}
                                                                @if ($detalle->producto->estado === 'inactivo')
                                                                    <span class="badge rounded-pill bg-warning text-dark">Inactivo</span>
                                                                @endif
                                                            @else
                                                                Producto no disponible
                                                            @endif
                                                        </td>
                                                        <td>{{ $detalle->cantidad }}</td>
                                                        <td>C$ {{ number_format($detalle->precio_unitario, 2) }}</td>
                                                        <td>C$ {{ number_format($detalle->iva, 2) }}</td>
                                                        <td>C$ {{ number_format($detalle->subtotal, 2) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="5" class="text-center">No hay detalles de productos.</td></tr>
                                                @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @elseif($detailsType === 'compra')
                                    <div class="col-md-6">
                                        <h4>Información Compra</h4>
                                        <p><strong>ID Compra:</strong> {{ $detailsItem->id }}</p>
                                        <p><strong>Fecha:</strong> {{ $detailsItem->fecha_compra ? \Carbon\Carbon::parse($detailsItem->fecha_compra)->format('d/m/Y') : 'N/A' }}</p>
                                        <p>
                                            <strong>Proveedor:</strong> 
                                            {{ $detailsItem->proveedor ? $detailsItem->proveedor->nombre : 'N/A' }}
                                            @if($detailsItem->proveedor && $detailsItem->proveedor->estado === 'inactivo')
                                                <span class="badge rounded-pill bg-warning text-dark">Inactivo</span>
                                            @endif
                                        </p>
                                        <p><strong>Empleado:</strong> {{ $detailsItem->empleado ? ($detailsItem->empleado->nombre . ' ' . $detailsItem->empleado->apellido) : 'N/A' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h4>Detalles</h4>
                                        <p><strong>Total:</strong> C$ {{ number_format($detailsItem->total, 2) }}</p>
                                        <p><strong>Descripción:</strong> {{ $detailsItem->descripcion ?? '-' }}</p>
                                    </div>
                                    <div class="col-12 mt-3">
                                        <h4>Productos Comprados</h4>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead>
                                                    <tr><th>Producto</th><th>Cant.</th><th>P. Unit.</th><th>Subtotal</th></tr>
                                                </thead>
                                                <tbody>
                                                @forelse($detailsItem->detalles as $detalle)
                                                    <tr>
                                                        <td>
                                                            @if ($detalle->producto)
                                                                {{ implode(', ', array_filter([
                                                                    $detalle->producto->nombre,
                                                                    $detalle->producto->marca,
                                                                    $detalle->producto->modelo
                                                                ])) }}
                                                                @if ($detalle->producto->estado === 'inactivo')
                                                                    <span class="badge rounded-pill bg-warning text-dark">Inactivo</span>
                                                                @endif
                                                            @else
                                                                Producto no disponible
                                                            @endif
                                                        </td>
                                                        <td>{{ $detalle->cantidad }}</td>
                                                        <td>C$ {{ number_format($detalle->precio_unitario, 2) }}</td>
                                                        <td>C$ {{ number_format($detalle->subtotal, 2) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="4" class="text-center">No hay detalles de productos.</td></tr>
                                                @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @elseif($detailsType === 'movimiento')
                                    <div class="col-md-6">
                                        <h4>Movimiento</h4>
                                        <p><strong>ID:</strong> {{ $detailsItem->id }}</p>
                                        <p><strong>Fecha:</strong> {{ $detailsItem->fecha ? \Carbon\Carbon::parse($detailsItem->fecha)->format('d/m/Y H:i') : 'N/A' }}</p>
                                        <p><strong>Tipo:</strong> <span class="badge rounded-pill bg-{{ $detailsItem->tipo === 'entrada' ? 'success' : 'danger' }}">{{ ucfirst($detailsItem->tipo) }}</span></p>
                                        <p>
                                            <strong>Producto:</strong> 
                                            @if ($detailsItem->producto)
                                                {{ implode(', ', array_filter([
                                                    $detailsItem->producto->nombre,
                                                    $detailsItem->producto->marca,
                                                    $detailsItem->producto->modelo
                                                ])) }}
                                                @if ($detailsItem->producto->estado === 'inactivo')
                                                    <span class="badge rounded-pill bg-warning text-dark">Inactivo</span>
                                                @endif
                                                (ID: {{ $detailsItem->id_producto }})
                                            @else
                                                Producto no disponible (ID: {{ $detailsItem->id_producto }})
                                            @endif
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <h4>Detalles</h4>
                                        <p><strong>Cantidad:</strong> {{ $detailsItem->cantidad }}</p>
                                        <p><strong>Origen (ID):</strong> {{ $detailsItem->id_origen ?? '-' }}</p>
                                        <p><strong>Descripción:</strong> {{ $detailsItem->descripcion ?? '-' }}</p>
                                    </div>
                                @else
                                    <div class="col-12">
                                        <p>No se pueden mostrar los detalles para este tipo de elemento.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeDetailsModal">Cerrar</button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@livewireScripts
</body>
</html>
</div>

