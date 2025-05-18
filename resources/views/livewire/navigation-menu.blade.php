<div>
<!DOCTYPE html>
<html lang="es" id="htmlElement" class="{{ $darkMode ? 'dark' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moto Repuesto Divino Niño</title>
    
    <link rel="stylesheet" href="{{ asset('css/stylesmenu.css') }}">
    <link href="https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css" rel="stylesheet">

    @livewireStyles
</head>
<body id="bodyElement" class="{{ $darkMode ? 'dark' : '' }}">
    <!-- Barra lateral -->
    <nav class="sidebar {{ $sidebarClosed ? 'close' : '' }}" id="sidebarElement">
        <header>
            <div class="image-text">
                <span class="image">
                    <img src="{{ asset('Images/Recurso 29.png') }}" alt="Logo">
                </span>
                <div class="text logo-text">
                    <span class="name">Moto Repuesto</span>
                    <span class="profession">Divino Niño</span>
                </div>
            </div>
            <i class='bx bx-chevron-right toggle' wire:click="toggleSidebar"></i>
        </header>

        <div class="menu-bar">
            <div class="menu">
                <ul class="menu-links">
                    @if($accessibleSections['inicio'])
                    <li class="nav-link">
                        <a href="#" wire:click.prevent="navigate('inicio')" class="{{ $activeSection === 'inicio' ? 'active' : '' }}">
                            <i class='bx bx-home-alt icon'></i>
                            <span class="text nav-text">Inicio</span>
                        </a>
                    </li>
                    @endif
                    @if($accessibleSections['facturacion'])
                    <li class="nav-link">
                        <a href="#" wire:click.prevent="navigate('facturacion')" class="{{ $activeSection === 'facturacion' ? 'active' : '' }}">
                            <i class='bx bx-receipt icon'></i>
                            <span class="text nav-text">Facturación</span>
                        </a>
                    </li>
                    @endif
                    @if($accessibleSections['inventario'])
                    <li class="nav-link">
                        <a href="#" wire:click.prevent="navigate('inventario')" class="{{ $activeSection === 'inventario' ? 'active' : '' }}">
                            <i class='bx bx-box icon'></i>
                            <span class="text nav-text">Inventario</span>
                        </a>
                    </li>
                    @endif
                    @if($accessibleSections['compras'])
                    <li class="nav-link">
                        <a href="#" wire:click.prevent="navigate('compras')" class="{{ $activeSection === 'compras' ? 'active' : '' }}">
                            <i class='bx bx-cart icon'></i>
                            <span class="text nav-text">Compras</span>
                        </a>
                    </li>
                    @endif
                    @if($accessibleSections['clientes'])
                    <li class="nav-link">
                        <a href="#" wire:click.prevent="navigate('clientes')" class="{{ $activeSection === 'clientes' ? 'active' : '' }}">
                            <i class='bx bx-group icon'></i>
                            <span class="text nav-text">Clientes</span>
                        </a>
                    </li>
                    @endif
                    @if($accessibleSections['proveedores'])
                    <li class="nav-link">
                        <a href="#" wire:click.prevent="navigate('proveedores')" class="{{ $activeSection === 'proveedores' ? 'active' : '' }}">
                            <i class='bx bx-store icon'></i>
                            <span class="text nav-text">Proveedores</span>
                        </a>
                    </li>
                    @endif
                    @if($accessibleSections['empleados'])
                    <li class="nav-link">
                        <a href="#" wire:click.prevent="navigate('empleados')" class="{{ $activeSection === 'empleados' ? 'active' : '' }}">
                            <i class='bx bx-user icon'></i>
                            <span class="text nav-text">Empleados</span>
                        </a>
                    </li>
                    @endif
                    @if($accessibleSections['reportes'])
                    <li class="nav-link">
                        <a href="#" wire:click.prevent="navigate('reportes')" class="{{ $activeSection === 'reportes' ? 'active' : '' }}">
                            <i class='bx bx-line-chart icon'></i>
                            <span class="text nav-text">Reportes</span>
                        </a>
                    </li>
                    @endif
                </ul>
            </div>

            <div class="bottom-content">
                <li>
                    <a href="#" wire:click.prevent="cerrarSesion">
                        <i class='bx bx-log-out icon'></i>
                        <span class="text nav-text">Cerrar Sesión</span>
                    </a>
                </li>

                <li class="mode">
                    <div class="sun-moon">
                        <i class='bx bx-moon icon moon'></i>
                        <i class='bx bx-sun icon sun'></i>
                    </div>
                    <span class="mode-text text" id="modeText">{{ $darkMode ? 'Modo Claro' : 'Modo Oscuro' }}</span>
                    <div class="toggle-switch" id="darkModeToggle">
                        <span class="switch {{ $darkMode ? 'dark' : '' }}" id="switchElement"></span>
                    </div>
                </li>
            </div>
        </div>
    </nav>

    <!-- Modal de Confirmación -->
    @if($showNavigationConfirmation)
    <div class="modal" style="display: flex;">
        <div class="modal-content">
            <span class="modal-close" wire:click="cancelNavigation">×</span>
            <h2><i class='bx bx-warning'></i> Cambios no guardados</h2>
            <p>Hay datos no guardados en la sección de Compras. ¿Estás seguro de que deseas salir? Los datos no guardados se perderán.</p>
            <div class="form-actions">
                <button type="button" class="btn btn-primary" wire:click="confirmNavigation">
                    <i class='bx bx-check'></i> Sí, salir
                </button>
                <button type="button" class="btn btn-secondary" wire:click="cancelNavigation">
                    <i class='bx bx-x'></i> Cancelar
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal para Respaldo de Base de Datos -->
    @if($showDatabaseModal)
    <div class="modal" style="display: flex;">
        <div class="modal-content">
            <span class="modal-close" wire:click="closeDatabaseModal">×</span>
            <h2><i class='bx bx-data'></i> Respaldo de Base de Datos</h2>
            @livewire('database-manager')
        </div>
    </div>
    @endif

    <section class="home {{ $darkMode ? 'dark' : '' }}" id="homeElement">
        <div id="inicio-content" class="section-content" style="{{ $activeSection !== 'inicio' ? 'display: none;' : '' }}">
            <div class="logo-full">
                <img src="{{ asset('Images/Recurso 28.png') }}" alt="Logo Completo">
            </div>

            <div class="dashboard-container">
                @if($activeSection === 'inicio' && $accessibleSections['database-backup'])
                <div class="database-backup-button">
                    <button class="btn-backup" wire:click="openDatabaseModal">
                        <i class='bx bx-data'></i> Realizar Respaldo
                    </button>
                </div>
                @endif

                <div class="dashboard-indicators">
                    <div class="indicator-card">
                        <h3>Ventas del Día</h3>
                        <p>{{ $ventasDia ?? 'C$0,00' }}</p>
                        <span class="trend {{ $variacionVentasDia >= 0 ? 'up' : 'down' }}">
                            {{ $variacionVentasDia !== null ? ($variacionVentasDia >= 0 ? '+' : '') . number_format($variacionVentasDia, 2, ',', '.') . '%' : '' }} desde ayer
                        </span>
                    </div>
                    <div class="indicator-card">
                        <h3>Productos Vendidos Hoy</h3>
                        <p>{{ $productosVendidosHoy ?? 0 }}</p>
                        <span class="trend {{ $variacionProductosVendidos >= 0 ? 'up' : 'down' }}">
                            {{ $variacionProductosVendidos !== null ? ($variacionProductosVendidos >= 0 ? '+' : '') . number_format($variacionProductosVendidos, 2, ',', '.') . '%' : '' }} desde ayer
                        </span>
                    </div>
                    <div class="indicator-card">
                        <h3>Nuevos Clientes</h3>
                        <p>{{ $nuevosClientes ?? 0 }}</p>
                        <span class="trend up">{{ $variacionNuevosClientes !== null ? '+' . number_format($variacionNuevosClientes, 2, ',', '.') . '%' : '' }} desde ayer</span>
                    </div>
                    <div class="indicator-card warning">
                        <h3>Productos Bajos</h3>
                        <p>{{ $productosBajos ?? 0 }}</p>
                        <span class="trend warning">Requieren atención</span>
                    </div>
                </div>

                <div class="dashboard-content">
                    <div class="chart-section">
                        <h3>Ventas Mensuales</h3>
                        <canvas id="ventasMensualesChart" height="200"></canvas>
                    </div>

                    <div class="list-section">
                        <h3>Productos Más Vendidos</h3>
                        <ul class="product-list">
                            @foreach ($productosMasVendidos ?? [] as $producto)
                                <li>{{ $producto['nombre'] }} - SKU: {{ $producto['sku'] }} <span>{{ $producto['cantidad'] }} uds</span></li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="dashboard-tables">
                    <div class="table-section">
                        <h3>Últimas Ventas</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Factura</th>
                                    <th>Monto</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ultimasVentas ?? [] as $venta)
                                    <tr>
                                        <td>{{ $venta['cliente'] }}</td>
                                        <td>{{ $venta['factura'] }}</td>
                                        <td>{{ $venta['monto'] }}</td>
                                        <td class="status {{ $venta['estado'] === 'Pagado' ? 'paid' : 'pending' }}">{{ $venta['estado'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="table-section">
                        <h3>Productos con Bajo Stock</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>SKU</th>
                                    <th>Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($productosBajosStock ?? [] as $producto)
                                    <tr>
                                        <td>{{ $producto['nombre'] }}</td>
                                        <td>{{ $producto['sku'] }}</td>
                                        <td>{{ $producto['stock'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="footer-buttons">
                <a href="{{ route('manual-usuario') }}" class="btn-footer">Cómo Usar el Sistema</a>
                <a href="{{ route('creadores') }}" class="btn-footer">Sobre los Creadores</a>
            </div>
        </div>

        @if($accessibleSections['facturacion'])
        <div id="facturacion-content" class="section-content" style="{{ $activeSection !== 'facturacion' ? 'display: none;' : '' }}">
            @if($activeSection === 'facturacion')
                @livewire('facturacion')
            @endif
        </div>
        @endif

        @if($accessibleSections['inventario'])
        <div id="inventario-content" class="section-content" style="{{ $activeSection !== 'inventario' ? 'display: none;' : '' }}">
            @if($activeSection === 'inventario')
                @livewire('inventario')
            @endif
        </div>
        @endif

        @if($accessibleSections['compras'])
        <div id="compras-content" class="section-content" style="{{ $activeSection !== 'compras' ? 'display: none;' : '' }}">
            @if($activeSection === 'compras')
                @livewire('compras')
            @endif
        </div>
        @endif

        @if($accessibleSections['clientes'])
        <div id="clientes-content" class="section-content" style="{{ $activeSection !== 'clientes' ? 'display: none;' : '' }}">
            @if($activeSection === 'clientes')
                @livewire('clientes')
            @endif
        </div>
        @endif

        @if($accessibleSections['proveedores'])
        <div id="proveedores-content" class="section-content" style="{{ $activeSection !== 'proveedores' ? 'display: none;' : '' }}">
            @if($activeSection === 'proveedores')
                @livewire('proveedores')
            @endif
        </div>
        @endif

        @if($accessibleSections['empleados'])
        <div id="empleados-content" class="section-content" style="{{ $activeSection !== 'empleados' ? 'display: none;' : '' }}">
            @if($activeSection === 'empleados')
                @livewire('empleados')
            @endif
        </div>
        @endif

        @if($accessibleSections['reportes'])
        <div id="reportes-content" class="section-content" style="{{ $activeSection !== 'reportes' ? 'display: none;' : '' }}">
            @if($activeSection === 'reportes')
                @livewire('reportes')
            @endif
        </div>
        @endif
    </section>

    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        let darkMode = {{ $darkMode ? 'true' : 'false' }};
        const htmlElement = document.getElementById('htmlElement');
        const bodyElement = document.getElementById('bodyElement');
        const homeElement = document.getElementById('homeElement');
        const sidebarElement = document.getElementById('sidebarElement');
        const switchElement = document.getElementById('switchElement');
        const modeText = document.getElementById('modeText');
        const darkModeToggle = document.getElementById('darkModeToggle');

        function updateDarkModeUI(isDark) {
            if (isDark) {
                htmlElement.classList.add('dark');
                bodyElement.classList.add('dark');
                homeElement.classList.add('dark');
                sidebarElement.classList.add('dark');
                switchElement.classList.add('dark');
                modeText.textContent = 'Modo Claro';
            } else {
                htmlElement.classList.remove('dark');
                bodyElement.classList.remove('dark');
                homeElement.classList.remove('dark');
                sidebarElement.classList.remove('dark');
                switchElement.classList.remove('dark');
                modeText.textContent = 'Modo Oscuro';
            }
        }

        function syncDarkModeWithServer(isDark) {
            fetch('/toggle-dark-mode', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ darkMode: isDark })
            }).catch(error => console.error('Error al sincronizar modo oscuro:', error));
        }

        darkModeToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            darkMode = !darkMode;
            updateDarkModeUI(darkMode);
            localStorage.setItem('darkMode', darkMode ? 'true' : 'false');
            syncDarkModeWithServer(darkMode);
        });

        const savedDarkMode = localStorage.getItem('darkMode');
        if (savedDarkMode !== null) {
            darkMode = savedDarkMode === 'true';
            updateDarkModeUI(darkMode);
            if (darkMode !== {{ $darkMode ? 'true' : 'false' }}) {
                syncDarkModeWithServer(darkMode);
            }
        }

        const ctx = document.getElementById('ventasMensualesChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($meses),
                datasets: [{
                    label: 'Ventas Mensuales (C$)',
                    data: @json($ventasMensuales),
                    backgroundColor: '#1a73e8',
                    borderColor: '#1a73e8',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'C$' + value.toLocaleString('es-NI', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            }
                        }
                    }
                }
            }
        });
    });
    </script>
</body>
</html>
</div>