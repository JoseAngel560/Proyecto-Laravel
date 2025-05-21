<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Session;
use App\Models\Empleado;
use App\Models\Factura;
use App\Models\DetalleFactura;
use App\Models\Producto;
use App\Models\Cliente;
use App\Livewire\Compras;
use App\Livewire\Facturacion;
use App\Livewire\Devoluciones;
use Carbon\Carbon;

class NavigationMenu extends Component
{
    public $sidebarClosed = false;
    public $darkMode = false;
    public $activeSection = 'inicio';
    public $showNavigationConfirmation = false;
    public $pendingSection = '';
    public $showDatabaseModal = false;

    public $ventasDia = null;
    public $variacionVentasDia = null;
    public $productosVendidosHoy = null; 
    public $variacionProductosVendidos = null;
    public $nuevosClientes = null;
    public $variacionNuevosClientes = null;
    public $productosBajos = null;
    public $productosMasVendidos = [];
    public $ultimasVentas = [];
    public $productosBajosStock = [];
    public $meses = [];
    public $ventasMensuales = [];

    protected $listeners = [
        'refreshNavigation' => '$refresh',
        'toggleDarkMode' => 'toggleDarkMode',
        'has-unsaved-changes' => 'handleUnsavedChanges'
    ];

    // Inicializa el componente y carga los datos del dashboard
    public function mount()
    {
        $this->darkMode = session('darkMode', false);
        $this->loadDashboardData();
    }

    // Alterna el estado de la barra lateral (cerrada/abierta)
    public function toggleSidebar()
    {
        $this->sidebarClosed = !$this->sidebarClosed;
    }

    // Alterna el modo oscuro y guarda la preferencia en la sesión
    public function toggleDarkMode()
    {
        $this->darkMode = !$this->darkMode;
        session(['darkMode' => $this->darkMode]);
        $this->dispatch('darkModeChanged', $this->darkMode);
    }

    // Maneja la navegación entre secciones, verificando cambios no guardados
    public function navigate($section)
    {

        
        if (in_array($this->activeSection, ['compras', 'facturacion', 'devoluciones']) && $section !== $this->activeSection) {
            $this->pendingSection = $section;
            $targetClass = match ($this->activeSection) {
                'compras' => Compras::class,
                'facturacion' => Facturacion::class,
                'devoluciones' => Devoluciones::class,
            };
            $this->dispatch('check-unsaved-changes')->to($targetClass);
            return;
        }

        $this->performNavigation($section);
    }

    // Maneja la respuesta de cambios no guardados antes de navegar
    #[On('has-unsaved-changes')]
    public function handleUnsavedChanges($hasChanges)
    {
        if ($hasChanges) {
            $this->showNavigationConfirmation = true;
        } else {
            $this->performNavigation($this->pendingSection);
        }
    }

    // Confirma la navegación a la sección pendiente
    public function confirmNavigation()
    {
        $this->showNavigationConfirmation = false;
        $section = $this->pendingSection;
        $this->pendingSection = '';

        if ($section === 'logout') {
            $this->cerrarSesionConfirmed();
        } else {
            $this->performNavigation($section);
        }
    }

    // Cancela la navegación y limpia la sección pendiente
    public function cancelNavigation()
    {
        $this->showNavigationConfirmation = false;
        $this->pendingSection = '';
    }

    // Realiza la navegación a una sección verificando permisos
    public function performNavigation($section)
    {
        $empleado = Empleado::find(Session::get('empleado_id'));
        if ($empleado && $empleado->hasAccessTo($section)) {
            $this->activeSection = $section;
            if ($section === 'inicio') {
                $this->loadDashboardData();
            }
        } else {
            session()->flash('error', 'No tienes acceso a esta sección.');
            $this->activeSection = 'inicio';
        }
    }

    // Inicia el proceso de cierre de sesión, verificando cambios no guardados
    public function cerrarSesion()
    {
        if (in_array($this->activeSection, ['compras', 'facturacion', 'devoluciones'])) {
            $this->pendingSection = 'logout';
            $targetClass = match ($this->activeSection) {
                'compras' => Compras::class,
                'facturacion' => Facturacion::class,
                'devoluciones' => Devoluciones::class,
            };
            $this->dispatch('check-unsaved-changes')->to($targetClass);
            return;
        }

        $this->cerrarSesionConfirmed();
    }

    // Cierra la sesión y redirige al login
    public function cerrarSesionConfirmed()
    {
        Session::flush();
        return redirect()->route('login');
    }

    // Abre el modal para la gestión de la base de datos
    public function openDatabaseModal()
    {
        $this->showDatabaseModal = true;
    }

    // Cierra el modal de gestión de la base de datos
// Cierra el modal de gestión de la base de datos
public function closeDatabaseModal()
{
    $this->showDatabaseModal = false;
    $this->dispatch('modalClosed'); // 🔥 Emitir evento para actualizar el gráfico
}


       private function loadDashboardData()
    {
        // Ventas del Día
        $hoy = Carbon::today();
        $ayer = Carbon::yesterday();
        $ventasHoy = Factura::whereDate('fecha_factura', $hoy)->sum('total');
        $ventasAyer = Factura::whereDate('fecha_factura', $ayer)->sum('total');
        $this->ventasDia = 'C$' . number_format($ventasHoy, 2, ',', '.');
        $this->variacionVentasDia = $ventasAyer > 0 ? (($ventasHoy - $ventasAyer) / $ventasAyer) * 100 : 0;

        // Productos Vendidos Hoy
        $facturasHoy = Factura::whereDate('fecha_factura', $hoy)->pluck('id');
        $facturasAyer = Factura::whereDate('fecha_factura', $ayer)->pluck('id');
        $productosVendidosHoy = DetalleFactura::whereIn('id_factura', $facturasHoy)->sum('cantidad');
        $productosVendidosAyer = DetalleFactura::whereIn('id_factura', $facturasAyer)->sum('cantidad');
        $this->productosVendidosHoy = $productosVendidosHoy;
        $this->variacionProductosVendidos = $productosVendidosAyer > 0 ? (($productosVendidosHoy - $productosVendidosAyer) / $productosVendidosAyer) * 100 : 0;

        // Nuevos Clientes
        $clientesNuevos = Cliente::whereDate('created_at', $hoy)->count();
        $clientesNuevosAyer = Cliente::whereDate('created_at', $ayer)->count();
        $this->nuevosClientes = $clientesNuevos;
        $this->variacionNuevosClientes = $clientesNuevosAyer > 0 ? (($clientesNuevos - $clientesNuevosAyer) / $clientesNuevosAyer) * 100 : 0;

        // Productos Bajos
        $this->productosBajos = Producto::where('stock', '<', 3)->where('estado', 'activo')->count();

        // Productos Más Vendidos
        $this->productosMasVendidos = DetalleFactura::select(
            'productos.nombre',
            'productos.id as codigo',
            'categorias.nombre as categoria',
            \DB::raw('SUM(detalle_factura.cantidad) as cantidad')
        )
            ->join('productos', 'detalle_factura.id_producto', '=', 'productos.id')
            ->join('categorias', 'productos.id_categoria', '=', 'categorias.id')
            ->groupBy('productos.id', 'productos.nombre', 'categorias.nombre')
            ->orderByDesc('cantidad')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'categoria' => $item->categoria,
                    'nombre' => $item->nombre,
                    'codigo' => 'COD-' . str_pad($item->codigo, 3, '0', STR_PAD_LEFT),
                    'cantidad' => $item->cantidad
                ];
            })->toArray();

        // Últimas Ventas
        $this->ultimasVentas = Factura::with('cliente', 'detalles.producto')
            ->orderBy('fecha_factura', 'desc')
            ->limit(3)
            ->get()
            ->map(function ($factura) {
                return [
                    'cliente' => $factura->cliente->nombre . ' ' . $factura->cliente->apellido,
                    'factura' => '#F-' . $factura->id,
                    'monto' => 'C$' . number_format($factura->total, 2, ',', '.'),
                    'estado' => $factura->totalcancelado >= $factura->total ? 'Pagado' : 'Pendiente'
                ];
            })->toArray();

        // Productos con Bajo Stock
        $this->productosBajosStock = Producto::select(
            'productos.nombre',
            'productos.id as codigo',
            'productos.stock',
            'categorias.nombre as categoria'
        )
            ->join('categorias', 'productos.id_categoria', '=', 'categorias.id')
            ->where('productos.stock', '<', 3)
            ->where('productos.estado', 'activo')
            ->get()
            ->map(function ($producto) {
                return [
                    'categoria' => $producto->categoria,
                    'nombre' => $producto->nombre,
                    'codigo' => 'COD-' . str_pad($producto->codigo, 3, '0', STR_PAD_LEFT),
                    'stock' => $producto->stock
                ];
            })->toArray();

        // Ventas Mensuales
        $this->meses = [
            'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'
        ];
        $ventasPorMes = Factura::selectRaw('MONTH(fecha_factura) as mes, SUM(total) as total')
            ->whereYear('fecha_factura', Carbon::now()->year)
            ->groupBy('mes')
            ->orderBy('mes')
            ->get()
            ->pluck('total', 'mes')
            ->toArray();
        $this->ventasMensuales = array_map(function ($mes) use ($ventasPorMes) {
            return $ventasPorMes[$mes] ?? 0;
        }, range(1, 12));
    }

    // Renderiza la vista del componente con las secciones accesibles
    public function render()
    {
        $empleado = Empleado::find(Session::get('empleado_id'));
        $accessibleSections = [
            'inicio' => true,
            'facturacion' => $empleado ? $empleado->hasAccessTo('facturacion') : false,
            'devoluciones' => $empleado ? $empleado->hasAccessTo('devoluciones') : false, 
            'inventario' => $empleado ? $empleado->hasAccessTo('inventario') : false,
            'compras' => $empleado ? $empleado->hasAccessTo('compras') : false,
            'clientes' => $empleado ? $empleado->hasAccessTo('clientes') : false,
            'proveedores' => $empleado ? $empleado->hasAccessTo('proveedores') : false,
            'empleados' => $empleado ? $empleado->hasAccessTo('empleados') : false,
            'reportes' => $empleado ? $empleado->hasAccessTo('reportes') : false,
            'database-backup' => $empleado ? $empleado->hasAccessTo('database-backup') : false,
        ];

        return view('livewire.navigation-menu', [
            'accessibleSections' => $accessibleSections,
            'ventasDia' => $this->ventasDia,
            'variacionVentasDia' => $this->variacionVentasDia,
            'productosVendidosHoy' => $this->productosVendidosHoy, 
            'variacionProductosVendidos' => $this->variacionProductosVendidos,
            'nuevosClientes' => $this->nuevosClientes,
            'variacionNuevosClientes' => $this->variacionNuevosClientes,
            'productosBajos' => $this->productosBajos,
            'productosMasVendidos' => $this->productosMasVendidos,
            'ultimasVentas' => $this->ultimasVentas,
            'productosBajosStock' => $this->productosBajosStock,
            'meses' => $this->meses,
            'ventasMensuales' => $this->ventasMensuales,
        ])->layout('layouts.app');
    }
}