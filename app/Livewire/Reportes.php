<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Factura;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\MovimientoInventario;
use App\Models\Categoria;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\VentasListaExport;
use App\Exports\VentaDetalleExport;
use App\Exports\ComprasListaExport;
use App\Exports\CompraDetalleExport;
use App\Exports\ProductosListaExport;
use App\Exports\ProveedoresListaExport;
use App\Exports\ClientesListaExport;
use App\Exports\EmpleadosListaExport;
use App\Exports\MovimientosListaExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class Reportes extends Component
{
    public $activeTab = 'ventas';
    public $busqueda = '';
    
    public $ventasFilterType = 'date';
    public $ventasStartDate, $ventasEndDate, $facturaId;

    public $comprasFilterType = 'date';
    public $comprasStartDate, $comprasEndDate, $compraId;

    public $productosCategoria = 'todas';
    public $productosOrden = 'nombre_asc';
    public $busquedaProducto = '';

    public $movimientosProducto = '';
    public $movimientosTipo = '';
    public $movimientosStartDate, $movimientosEndDate;

    public $showDetailsModal = false;
    public $detailsItem = null;
    public $detailsType = '';

    protected $queryString = [
        'activeTab' => ['except' => 'ventas'],
        'busqueda' => ['except' => ''],
        'busquedaProducto' => ['except' => ''],
        'ventasFilterType' => ['except' => 'date'],
        'facturaId' => ['except' => null],
        'comprasFilterType' => ['except' => 'date'],
        'compraId' => ['except' => null],
    ];

    // Construye la consulta para obtener las ventas con filtros aplicados
    private function getVentasQuery()
    {
        if ($this->ventasFilterType === 'id' && $this->facturaId) {
            return Factura::query()
                ->with([
                    'detalles.producto' => function ($query) {
                        $query->withoutGlobalScope('active');
                    },
                    'cliente' => function ($query) {
                        $query->withoutGlobalScope('active');
                    },
                    'empleado'
                ])
                ->where('id', $this->facturaId);
        }

        return Factura::query()
            ->with([
                'cliente' => function ($query) {
                    $query->withoutGlobalScope('active');
                },
                'empleado'
            ])
            ->when($this->ventasFilterType === 'date', function ($q) {
                if ($this->ventasStartDate) {
                    $q->whereDate('fecha_factura', '>=', $this->ventasStartDate);
                }
                if ($this->ventasEndDate) {
                    $q->whereDate('fecha_factura', '<=', \Carbon\Carbon::parse($this->ventasEndDate)->endOfDay());
                }
            })
            ->orderBy('fecha_factura', 'desc');
    }

    // Construye la consulta para obtener las compras con filtros aplicados
    private function getComprasQuery()
    {
        if ($this->comprasFilterType === 'id' && $this->compraId) {
            return Compra::query()
                ->with([
                    'detalles.producto' => function ($query) {
                        $query->withoutGlobalScope('active');
                    },
                    'proveedor' => function ($query) {
                        $query->withoutGlobalScope('active');
                    },
                    'empleado'
                ])
                ->where('id', $this->compraId);
        }

        return Compra::query()
            ->with([
                'proveedor' => function ($query) {
                    $query->withoutGlobalScope('active');
                },
                'empleado'
            ])
            ->when($this->comprasFilterType === 'date', function ($q) {
                if ($this->comprasStartDate) {
                    $q->whereDate('fecha_compra', '>=', $this->comprasStartDate);
                }
                if ($this->comprasEndDate) {
                    $q->whereDate('fecha_compra', '<=', \Carbon\Carbon::parse($this->comprasEndDate)->endOfDay());
                }
            })
            ->orderBy('fecha_compra', 'desc');
    }

    // Construye la consulta para obtener los productos con filtros aplicados
    private function getProductosQuery()
    {
        $query = Producto::query()
            ->with('categoria')
            ->where('estado', 'activo')
            ->when($this->busquedaProducto, function ($q) {
                $q->where(function($subq) {
                    $subq->where('nombre', 'like', '%'.$this->busquedaProducto.'%')
                         ->orWhere('marca', 'like', '%'.$this->busquedaProducto.'%')
                         ->orWhere('modelo', 'like', '%'.$this->busquedaProducto.'%');
                });
            })
            ->when($this->productosCategoria !== 'todas', function ($q) {
                $q->where('id_categoria', $this->productosCategoria);
            });

        match ($this->productosOrden) {
            'nombre_desc' => $query->orderBy('nombre', 'desc'),
            'stock_asc' => $query->orderBy('stock', 'asc'),
            'stock_desc' => $query->orderBy('stock', 'desc'),
            default => $query->orderBy('nombre', 'asc'),
        };

        return $query;
    }

    // Construye la consulta para obtener los proveedores con filtros aplicados
    private function getProveedoresQuery()
    {
        return Proveedor::query()
            ->when($this->busqueda, function ($q) {
                 $q->where(function($subq) {
                    $subq->where('nombre', 'like', '%'.$this->busqueda.'%')
                         ->orWhere('ruc', 'like', '%'.$this->busqueda.'%')
                         ->orWhere('contacto', 'like', '%'.$this->busqueda.'%');
                 });
            })
            ->orderBy('nombre', 'asc');
    }

    // Construye la consulta para obtener los clientes con filtros aplicados
    private function getClientesQuery()
    {
        return Cliente::query()
            ->when($this->busqueda, function ($q) {
                 $q->where(function($subq) {
                    $subq->where('nombre', 'like', '%'.$this->busqueda.'%')
                         ->orWhere('apellido', 'like', '%'.$this->busqueda.'%')
                         ->orWhere('telefono', 'like', '%'.$this->busqueda.'%');
                 });
            })
            ->orderBy('nombre', 'asc');
    }

    // Construye la consulta para obtener los empleados con filtros aplicados
    private function getEmpleadosQuery()
    {
        return Empleado::query()
            ->when($this->busqueda, function ($q) {
                 $q->where(function($subq) {
                    $subq->where('nombre', 'like', '%'.$this->busqueda.'%')
                         ->orWhere('apellido', 'like', '%'.$this->busqueda.'%')
                         ->orWhere('cedula', 'like', '%'.$this->busqueda.'%')
                         ->orWhere('cargo', 'like', '%'.$this->busqueda.'%');
                 });
            })
            ->orderBy('nombre', 'asc');
    }

    // Construye la consulta para obtener los movimientos de inventario con filtros aplicados
    private function getMovimientosQuery()
    {
        return MovimientoInventario::query()
            ->with(['producto' => function ($query) {
                $query->withoutGlobalScope('active');
            }])
            ->when($this->movimientosProducto, function ($q) {
                $q->whereHas('producto', function ($subQ) {
                    $subQ->withoutGlobalScope('active')
                         ->where('nombre', 'like', '%' . $this->movimientosProducto . '%');
                });
            })
            ->when($this->movimientosTipo, function ($q) {
                $q->where('tipo', $this->movimientosTipo);
            })
            ->when($this->movimientosStartDate, function ($q) {
                $q->whereDate('fecha', '>=', $this->movimientosStartDate);
            })
            ->when($this->movimientosEndDate, function ($q) {
                $q->whereDate('fecha', '<=', \Carbon\Carbon::parse($this->movimientosEndDate)->endOfDay());
            })
            ->orderBy('fecha', 'desc');
    }

    // Renderiza la vista con los datos filtrados
    public function render()
    {
        $data = [];
        $errorMessage = null;

        try {
            if (!isset($data['categorias'])) {
                try {
                    $data['categorias'] = Categoria::orderBy('nombre')->get();
                } catch (Exception $catEx) {
                    Log::error("Error cargando categorias: " . $catEx->getMessage());
                    $data['categorias'] = collect();
                    $errorMessage = "Error al cargar categorias.";
                }
            }

            switch ($this->activeTab) {
                case 'compras':
                    $data['compras'] = $this->getComprasQuery()->get();
                    break;
                case 'productos':
                    $data['productos'] = $this->getProductosQuery()->get();
                    break;
                case 'proveedores':
                    $data['proveedores'] = $this->getProveedoresQuery()->get();
                    break;
                case 'clientes':
                    $data['clientes'] = $this->getClientesQuery()->get();
                    break;
                case 'empleados':
                    $data['empleados'] = $this->getEmpleadosQuery()->get();
                    break;
                case 'movimientos':
                    $data['movimientos'] = $this->getMovimientosQuery()->get();
                    break;
                case 'ventas':
                default:
                    $this->activeTab = 'ventas';
                    $data['ventas'] = $this->getVentasQuery()->get();
                    break;
            }
        } catch (QueryException $e) {
            Log::error("Error de BD cargando {$this->activeTab}: " . $e->getMessage());
            $errorMessage = "Error de base de datos al cargar {$this->activeTab}. Verifica las columnas y relaciones.";
        } catch (Exception $e) {
            Log::error("Error general cargando {$this->activeTab}: " . $e->getMessage());
            $errorMessage = "Ocurrio un error inesperado al cargar los datos de {$this->activeTab}.";
        }

        if ($errorMessage) {
            session()->flash('error', $errorMessage . ' Revisa los logs para mas detalles.');
            $data['compras'] = collect();
            $data['productos'] = collect();
            $data['proveedores'] = collect();
            $data['clientes'] = collect();
            $data['empleados'] = collect();
            $data['movimientos'] = collect();
            $data['ventas'] = collect();
            if (!isset($data['categorias'])) {
                $data['categorias'] = collect();
            }
        }

        return view('livewire.reportes', $data);
    }

    // Cambia la pestaña activa y limpia filtros específicos
    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        $this->limpiarFiltrosEspecificos();
    }

    // Detecta cambios en las propiedades para actualizar la vista
    public function updated($propertyName)
    {
        if (str_contains($propertyName, 'Date') || str_contains($propertyName, 'Id') ||
            str_contains($propertyName, 'FilterType') || str_contains($propertyName, 'Categoria') ||
            str_contains($propertyName, 'Orden') || str_contains($propertyName, 'Tipo') ||
            $propertyName === 'busqueda' || $propertyName === 'busquedaProducto' || $propertyName === 'movimientosProducto')
        {

        }
    }

    // Limpia todos los filtros generales
    public function limpiarFiltrosGenerales()
    {
        $this->reset([
            'busqueda', 'busquedaProducto',
            'ventasFilterType', 'ventasStartDate', 'ventasEndDate', 'facturaId',
            'comprasFilterType', 'comprasStartDate', 'comprasEndDate', 'compraId',
            'productosCategoria', 'productosOrden',
            'movimientosProducto', 'movimientosTipo', 'movimientosStartDate', 'movimientosEndDate'
        ]);
        $this->ventasFilterType = 'date';
        $this->comprasFilterType = 'date';
        $this->productosCategoria = 'todas';
        $this->productosOrden = 'nombre_asc';
    }

    // Limpia filtros específicos según la pestaña activa
    public function limpiarFiltrosEspecificos()
    {
        if (!in_array($this->activeTab, ['proveedores', 'clientes', 'empleados'])) {
            $this->busqueda = '';
        }
        if (!in_array($this->activeTab, ['productos', 'movimientos'])) {
            $this->busquedaProducto = '';
            $this->movimientosProducto = '';
        }
    }

    // Muestra el modal con detalles de una venta, compra o movimiento
    public function showDetailsModal($type, $id)
    {
        $this->detailsType = $type;
        $this->detailsItem = null;

        try {
            switch ($type) {
                case 'venta':
                    $this->detailsItem = Factura::with([
                        'cliente' => function ($query) {
                            $query->withoutGlobalScope('active');
                        },
                        'empleado',
                        'detalles.producto' => function ($query) {
                            $query->withoutGlobalScope('active');
                        }
                    ])->findOrFail($id);
                    break;
                case 'compra':
                    $this->detailsItem = Compra::with([
                        'proveedor' => function ($query) {
                            $query->withoutGlobalScope('active');
                        },
                        'empleado',
                        'detalles.producto' => function ($query) {
                            $query->withoutGlobalScope('active');
                        }
                    ])->findOrFail($id);
                    break;
                case 'movimiento':
                    $this->detailsItem = MovimientoInventario::with([
                        'producto' => function ($query) {
                            $query->withoutGlobalScope('active');
                        }
                    ])->findOrFail($id);
                    break;
                default:
                    Log::warning("showDetailsModal llamado con tipo no esperado: {$type}");
                    return;
            }
            $this->showDetailsModal = true;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("No se encontro {$type} con ID {$id}: " . $e->getMessage());
            session()->flash('error', "No se encontro el registro solicitado ({$type} ID: {$id}).");
            $this->closeDetailsModal();
        } catch (Exception $e) {
            Log::error("Error cargando detalles para {$type} ID {$id}: " . $e->getMessage());
            session()->flash('error', 'Error al cargar detalles: ' . $e->getMessage());
            $this->closeDetailsModal();
        }
    }

    // Cierra el modal de detalles
    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->detailsItem = null;
        $this->detailsType = '';
    }

    // Registra un clic de prueba en los logs
    public function testClick()
    {
        Log::info("Boton de prueba clicado");
        session()->flash('info', 'Boton de prueba clicado correctamente.');
    }

    // Exporta los datos de la pestaña activa en el formato especificado
    public function export($format)
    {
        Log::info("Metodo export llamado con formato: {$format} para pestaña: {$this->activeTab}");

        try {
            if (!in_array($format, ['xlsx', 'csv', 'pdf'])) {
                Log::warning("Formato no soportado: {$format}");
                session()->flash('error', 'Formato no soportado.');
                return;
            }

            $filename = ucfirst($this->activeTab) . '_' . now()->format('Ymd_His');

            $filters = [
                'busqueda' => $this->busqueda ?: 'Ninguna',
                'ventasFilterType' => $this->ventasFilterType,
                'ventasStartDate' => $this->ventasStartDate ?: 'N/A',
                'ventasEndDate' => $this->ventasEndDate ?: 'N/A',
                'facturaId' => $this->facturaId ?: 'N/A',
                'comprasFilterType' => $this->comprasFilterType,
                'comprasStartDate' => $this->comprasStartDate ?: 'N/A',
                'comprasEndDate' => $this->comprasEndDate ?: 'N/A',
                'compraId' => $this->compraId ?: 'N/A',
                'busquedaProducto' => $this->busquedaProducto ?: 'Ninguna',
                'productosCategoria' => $this->productosCategoria === 'todas' ? 'Todas' : ($this->productosCategoria ? Categoria::find($this->productosCategoria)->nombre : 'N/A'),
                'productosOrden' => match ($this->productosOrden) {
                    'nombre_asc' => 'Nombre (A-Z)',
                    'nombre_desc' => 'Nombre (Z-A)',
                    'stock_asc' => 'Stock (Menor a Mayor)',
                    'stock_desc' => 'Stock (Mayor a Menor)',
                    default => 'Nombre (A-Z)',
                },
                'movimientosProducto' => $this->movimientosProducto ?: 'Ninguna',
                'movimientosTipo' => $this->movimientosTipo ?: 'Todos',
                'movimientosStartDate' => $this->movimientosStartDate ?: 'N/A',
                'movimientosEndDate' => $this->movimientosEndDate ?: 'N/A',
            ];

            switch ($this->activeTab) {
                case 'ventas':
                    if ($format === 'pdf') {
                        if ($this->ventasFilterType === 'id' && $this->facturaId) {
                            $venta = $this->getVentasQuery()->first();
                            if (!$venta) {
                                Log::warning("No se encontro la factura con ID {$this->facturaId}");
                                session()->flash('error', 'No se encontro la factura con el ID especificado.');
                                return;
                            }
                            $detalles = $venta->detalles;
                            if ($detalles->isEmpty()) {
                                Log::warning("No hay detalles para la venta ID {$this->facturaId}");
                                session()->flash('error', 'No hay detalles para exportar.');
                                return;
                            }
                            $pdfFileName = 'venta_' . $venta->id . '_' . Str::random(10) . '. women';
                            $pdfPath = 'ventas/' . $pdfFileName;
                            $fullPath = storage_path('app/public/' . $pdfPath);

                            $pdf = Pdf::loadView('pdfs.venta_detalle', [
                                'venta' => $venta,
                                'cliente' => $venta->cliente,
                                'detalles' => $detalles,
                                'filters' => $filters,
                            ]);

                            Storage::disk('public')->put($pdfPath, $pdf->output());

                            if (!Storage::disk('public')->exists($pdfPath)) {
                                Log::error('PDF no encontrado en: ' . $pdfPath);
                                session()->flash('error', 'El PDF no se genero correctamente.');
                                return;
                            }

                            Log::info('PDF generado para venta ID ' . $venta->id, ['pdf_path' => $pdfPath]);
                            return response()->download($fullPath, 'comprobante_venta_FAC' . str_pad($venta->id, 6, '0', STR_PAD_LEFT) . '.pdf')->deleteFileAfterSend(true);
                        } else {
                            $ventas = $this->getVentasQuery()->get();
                            if ($ventas->isEmpty()) {
                                Log::warning("No hay ventas para exportar con los filtros aplicados");
                                session()->flash('error', 'No hay ventas para exportar con los filtros aplicados.');
                                return;
                            }
                            $pdfFileName = 'ventas_' . Str::random(10) . '.pdf';
                            $pdfPath = 'ventas/' . $pdfFileName;
                            $fullPath = storage_path('app/public/' . $pdfPath);

                            $pdf = Pdf::loadView('pdfs.ventas_lista', [
                                'ventas' => $ventas,
                                'filters' => $filters,
                            ]);

                            Storage::disk('public')->put($pdfPath, $pdf->output());

                            if (!Storage::disk('public')->exists($pdfPath)) {
                                Log::error('PDF no encontrado en: ' . $pdfPath);
                                session()->flash('error', 'El PDF no se genero correctamente.');
                                return;
                            }

                            Log::info('PDF generado para lista de ventas', ['pdf_path' => $pdfPath]);
                            return response()->download($fullPath, $filename . '.pdf')->deleteFileAfterSend(true);
                        }
                    } else {
                        if ($this->ventasFilterType === 'id' && $this->facturaId) {
                            $venta = $this->getVentasQuery()->first();
                            if (!$venta) {
                                Log::warning("No se encontro la factura con ID {$this->facturaId}");
                                session()->flash('error', 'No se encontro la factura con el ID especificado.');
                                return;
                            }
                            $detalles = $venta->detalles;
                            if ($detalles->isEmpty()) {
                                Log::warning("No hay detalles para la venta ID {$this->facturaId}");
                                session()->flash('error', 'No hay detalles para exportar.');
                                return;
                            }
                            Log::info("Exportando detalles de venta ID {$this->facturaId}, detalles: " . $detalles->count());
                            return Excel::download(new VentaDetalleExport($detalles), 'Detalles_Venta_' . $this->facturaId . '.' . $format);
                        } else {
                            $ventas = $this->getVentasQuery()->get();
                            if ($ventas->isEmpty()) {
                                Log::warning("No hay ventas para exportar con los filtros aplicados");
                                session()->flash('error', 'No hay ventas para exportar con los filtros aplicados.');
                                return;
                            }
                            Log::info("Exportando lista de ventas, count: {$ventas->count()}");
                            return Excel::download(new VentasListaExport($ventas), $filename . '.' . $format);
                        }
                    }
                    break;

                case 'compras':
                    if ($format === 'pdf') {
                        if ($this->comprasFilterType === 'id' && $this->compraId) {
                            $compra = $this->getComprasQuery()->first();
                            if (!$compra) {
                                Log::warning("No se encontro la compra con ID {$this->compraId}");
                                session()->flash('error', 'No se encontro la compra con el ID especificado.');
                                return;
                            }
                            $detalles = $compra->detalles;
                            if ($detalles->isEmpty()) {
                                Log::warning("No hay detalles para la compra ID {$this->compraId}");
                                session()->flash('error', 'No hay detalles para exportar.');
                                return;
                            }
                            $pdfFileName = 'compra_' . $compra->id . '_' . Str::random(10) . '.pdf';
                            $pdfPath = 'compras/' . $pdfFileName;
                            $fullPath = storage_path('app/public/' . $pdfPath);

                            $pdf = Pdf::loadView('pdfs.compra_detalle', [
                                'compra' => $compra,
                                'proveedor' => $compra->proveedor,
                                'detalles' => $detalles,
                                'filters' => $filters,
                            ]);

                            Storage::disk('public')->put($pdfPath, $pdf->output());

                            if (!Storage::disk('public')->exists($pdfPath)) {
                                Log::error('PDF no encontrado en: ' . $pdfPath);
                                session()->flash('error', 'El PDF no se genero correctamente.');
                                return;
                            }

                            Log::info('PDF generado para compra ID ' . $compra->id, ['pdf_path' => $pdfPath]);
                            return response()->download($fullPath, 'comprobante_compra_CMP' . str_pad($compra->id, 6, '0', STR_PAD_LEFT) . '.pdf')->deleteFileAfterSend(true);
                        } else {
                            $compras = $this->getComprasQuery()->get();
                            if ($compras->isEmpty()) {
                                Log::warning("No hay compras para exportar con los filtros aplicados");
                                session()->flash('error', 'No hay compras para exportar con los filtros aplicados.');
                                return;
                            }
                            $pdfFileName = 'compras_' . Str::random(10) . '.pdf';
                            $pdfPath = 'compras/' . $pdfFileName;
                            $fullPath = storage_path('app/public/' . $pdfPath);

                            $pdf = Pdf::loadView('pdfs.compras_lista', [
                                'compras' => $compras,
                                'filters' => $filters,
                            ]);

                            Storage::disk('public')->put($pdfPath, $pdf->output());

                            if (!Storage::disk('public')->exists($pdfPath)) {
                                Log::error('PDF no encontrado en: ' . $pdfPath);
                                session()->flash('error', 'El PDF no se genero correctamente.');
                                return;
                            }

                            Log::info('PDF generado para lista de compras', ['pdf_path' => $pdfPath]);
                            return response()->download($fullPath, $filename . '.pdf')->deleteFileAfterSend(true);
                        }
                    } else {
                        if ($this->comprasFilterType === 'id' && $this->compraId) {
                            $compra = $this->getComprasQuery()->first();
                            if (!$compra) {
                                Log::warning("No se encontro la compra con ID {$this->compraId}");
                                session()->flash('error', 'No se encontro la compra con el ID especificado.');
                                return;
                            }
                            $detalles = $compra->detalles;
                            if ($detalles->isEmpty()) {
                                Log::warning("No hay detalles para la compra ID {$this->compraId}");
                                session()->flash('error', 'No hay detalles para exportar.');
                                return;
                            }
                            Log::info("Exportando detalles de compra ID {$this->compraId}, detalles: " . $detalles->count());
                            return Excel::download(new CompraDetalleExport($detalles), 'Detalles_Compra_' . $this->compraId . '.' . $format);
                        } else {
                            $compras = $this->getComprasQuery()->get();
                            if ($compras->isEmpty()) {
                                Log::warning("No hay compras para exportar con los filtros aplicados");
                                session()->flash('error', 'No hay compras para exportar con los filtros aplicados.');
                                return;
                            }
                            Log::info("Exportando lista de compras, count: {$compras->count()}");
                            return Excel::download(new ComprasListaExport($compras), $filename . '.' . $format);
                        }
                    }
                    break;

                case 'productos':
                    if ($format === 'pdf') {
                        $productos = $this->getProductosQuery()->get();
                        if ($productos->isEmpty()) {
                            Log::warning("No hay productos para exportar con los filtros aplicados");
                            session()->flash('error', 'No hay productos para exportar con los filtros aplicados.');
                            return;
                        }
                        $pdfFileName = 'productos_' . Str::random(10) . '.pdf';
                        $pdfPath = 'productos/' . $pdfFileName;
                        $fullPath = storage_path('app/public/' . $pdfPath);

                        $pdf = Pdf::loadView('pdfs.productos_lista', [
                            'productos' => $productos,
                            'filters' => $filters,
                        ]);

                        Storage::disk('public')->put($pdfPath, $pdf->output());

                        if (!Storage::disk('public')->exists($pdfPath)) {
                            Log::error('PDF no encontrado en: ' . $pdfPath);
                            session()->flash('error', 'El PDF no se genero correctamente.');
                            return;
                        }

                        Log::info('PDF generado para lista de productos', ['pdf_path' => $pdfPath]);
                        return response()->download($fullPath, $filename . '.pdf')->deleteFileAfterSend(true);
                    } else {
                        $productos = $this->getProductosQuery()->get();
                        if ($productos->isEmpty()) {
                            Log::warning("No hay productos para exportar con los filtros aplicados");
                            session()->flash('error', 'No hay productos para exportar con los filtros aplicados.');
                            return;
                        }
                        Log::info("Exportando lista de productos, count: {$productos->count()}");
                        return Excel::download(new ProductosListaExport($productos), $filename . '.' . $format);
                    }
                    break;

                case 'proveedores':
                    if ($format === 'pdf') {
                        $proveedores = $this->getProveedoresQuery()->get();
                        if ($proveedores->isEmpty()) {
                            Log::warning("No hay proveedores para exportar con los filtros aplicados");
                            session()->flash('error', 'No hay proveedores para exportar con los filtros aplicados.');
                            return;
                        }
                        $pdfFileName = 'proveedores_' . Str::random(10) . '.pdf';
                        $pdfPath = 'proveedores/' . $pdfFileName;
                        $fullPath = storage_path('app/public/' . $pdfPath);

                        $pdf = Pdf::loadView('pdfs.proveedores_lista', [
                            'proveedores' => $proveedores,
                            'filters' => $filters,
                        ]);

                        Storage::disk('public')->put($pdfPath, $pdf->output());

                        if (!Storage::disk('public')->exists($pdfPath)) {
                            Log::error('PDF no encontrado en: ' . $pdfPath);
                            session()->flash('error', 'El PDF no se genero correctamente.');
                            return;
                        }

                        Log::info('PDF generado para lista de proveedores', ['pdf_path' => $pdfPath]);
                        return response()->download($fullPath, $filename . '.pdf')->deleteFileAfterSend(true);
                    } else {
                        $proveedores = $this->getProveedoresQuery()->get();
                        if ($proveedores->isEmpty()) {
                            Log::warning("No hay proveedores para exportar con los filtros aplicados");
                            session()->flash('error', 'No hay proveedores para exportar con los filtros aplicados.');
                            return;
                        }
                        Log::info("Exportando lista de proveedores, count: {$proveedores->count()}");
                        return Excel::download(new ProveedoresListaExport($proveedores), $filename . '.' . $format);
                    }
                    break;

                case 'clientes':
                    if ($format === 'pdf') {
                        $clientes = $this->getClientesQuery()->get();
                        if ($clientes->isEmpty()) {
                            Log::warning("No hay clientes para exportar con los filtros aplicados");
                            session()->flash('error', 'No hay clientes para exportar con los filtros aplicados.');
                            return;
                        }
                        $pdfFileName = 'clientes_' . Str::random(10) . '.pdf';
                        $pdfPath = 'clientes/' . $pdfFileName;
                        $fullPath = storage_path('app/public/' . $pdfPath);

                        $pdf = Pdf::loadView('pdfs.clientes_lista', [
                            'clientes' => $clientes,
                            'filters' => $filters,
                        ]);

                        Storage::disk('public')->put($pdfPath, $pdf->output());

                        if (!Storage::disk('public')->exists($pdfPath)) {
                            Log::error('PDF no encontrado en: ' . $pdfPath);
                            session()->flash('error', 'El PDF no se genero correctamente.');
                            return;
                        }

                        Log::info('PDF generado para lista de clientes', ['pdf_path' => $pdfPath]);
                        return response()->download($fullPath, $filename . '.pdf')->deleteFileAfterSend(true);
                    } else {
                        $clientes = $this->getClientesQuery()->get();
                        if ($clientes->isEmpty()) {
                            Log::warning("No hay clientes para exportar con los filtros aplicados");
                            session()->flash('error', 'No hay clientes para exportar con los filtros aplicados.');
                            return;
                        }
                        Log::info("Exportando lista de clientes, count: {$clientes->count()}");
                        return Excel::download(new ClientesListaExport($clientes), $filename . '.' . $format);
                    }
                    break;

                case 'empleados':
                    if ($format === 'pdf') {
                        $empleados = $this->getEmpleadosQuery()->get();
                        if ($empleados->isEmpty()) {
                            Log::warning("No hay empleados para exportar con los filtros aplicados");
                            session()->flash('error', 'No hay empleados para exportar con los filtros aplicados.');
                            return;
                        }
                        $pdfFileName = 'empleados_' . Str::random(10) . '.pdf';
                        $pdfPath = 'empleados/' . $pdfFileName;
                        $fullPath = storage_path('app/public/' . $pdfPath);

                        $pdf = Pdf::loadView('pdfs.empleados_lista', [
                            'empleados' => $empleados,
                            'filters' => $filters,
                        ]);

                        Storage::disk('public')->put($pdfPath, $pdf->output());

                        if (!Storage::disk('public')->exists($pdfPath)) {
                            Log::error('PDF no encontrado en: ' . $pdfPath);
                            session()->flash('error', 'El PDF no se genero correctamente.');
                            return;
                        }

                        Log::info('PDF generado para lista de empleados', ['pdf_path' => $pdfPath]);
                        return response()->download($fullPath, $filename . '.pdf')->deleteFileAfterSend(true);
                    } else {
                        $empleados = $this->getEmpleadosQuery()->get();
                        if ($empleados->isEmpty()) {
                            Log::warning("No hay empleados para exportar con los filtros aplicados");
                            session()->flash('error', 'No hay empleados para exportar con los filtros aplicados.');
                            return;
                        }
                        Log::info("Exportando lista de empleados, count: {$empleados->count()}");
                        return Excel::download(new EmpleadosListaExport($empleados), $filename . '.' . $format);
                    }
                    break;

                case 'movimientos':
                    if ($format === 'pdf') {
                        $movimientos = $this->getMovimientosQuery()->get();
                        if ($movimientos->isEmpty()) {
                            Log::warning("No hay movimientos para exportar con los filtros aplicados");
                            session()->flash('error', 'No hay movimientos para exportar con los filtros aplicados.');
                            return;
                        }
                        $pdfFileName = 'movimientos_' . Str::random(10) . '.pdf';
                        $pdfPath = 'movimientos/' . $pdfFileName;
                        $fullPath = storage_path('app/public/' . $pdfPath);

                        $pdf = Pdf::loadView('pdfs.movimientos_lista', [
                            'movimientos' => $movimientos,
                            'filters' => $filters,
                        ]);

                        Storage::disk('public')->put($pdfPath, $pdf->output());

                        if (!Storage::disk('public')->exists($pdfPath)) {
                            Log::error('PDF no encontrado en: ' . $pdfPath);
                            session()->flash('error', 'El PDF no se genero correctamente.');
                            return;
                        }

                        Log::info('PDF generado para lista de movimientos', ['pdf_path' => $pdfPath]);
                        return response()->download($fullPath, $filename . '.pdf')->deleteFileAfterSend(true);
                    } else {
                        $movimientos = $this->getMovimientosQuery()->get();
                        if ($movimientos->isEmpty()) {
                            Log::warning("No hay movimientos para exportar con los filtros aplicados");
                            session()->flash('error', 'No hay movimientos para exportar con los filtros aplicados.');
                            return;
                        }
                        Log::info("Exportando lista de movimientos, count: {$movimientos->count()}");
                        return Excel::download(new MovimientosListaExport($movimientos), $filename . '.' . $format);
                    }
                    break;

                default:
                    Log::warning("Pestaña no soportada para exportacion: {$this->activeTab}");
                    session()->flash('error', 'No se puede exportar para esta pestaña.');
                    return;
            }
        } catch (Exception $e) {
            Log::error("Error exportando {$this->activeTab} a {$format}: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            session()->flash('error', "Error al exportar a {$format}: " . $e->getMessage());
            return;
        }
    }
}