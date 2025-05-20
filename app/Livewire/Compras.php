<?php

namespace App\Livewire;

use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Categoria;
use App\Models\MovimientoInventario;
use App\Models\Temporalidad;
use App\Models\Empleado;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class Compras extends Component
{
    public $numero_compra;
    public $id_proveedor;
    public $fecha_compra;
    public $descripcion;
    public $total = 0;
    public $searchTermProveedor = '';
    public $searchTermCategoria = '';
    public $searchTermProducto = '';
    public $categoria_id;
    public $producto_id;
    public $showProveedorModal = false;
    public $showCategoriaModal = false;
    public $nuevoProveedor = [
        'nombre' => '',
        'razon_social' => '',
        'contacto' => '',
        'telefono' => '',
        'email' => '',
        'ruc' => '',
        'direccion' => ''
    ];
    public $nuevaCategoria = [
        'nombre' => '',
        'descripcion' => ''
    ];
    public $productos = [];
    public $proveedoresFiltrados = [];
    public $categoriasFiltradas = [];
    public $productosFiltrados = [];

   protected $rules = [
        'id_proveedor' => 'required|exists:proveedores,id',
        'fecha_compra' => 'required|date',
        'productos' => 'required|array|min:1',
        'productos.*.id_categoria' => 'required|exists:categorias,id',
        'productos.*.nombre' => 'required_if:productos.*.es_nuevo,true|string|max:255|not_regex:/^\s*$/',
        'productos.*.marca' => 'required|string|max:100|not_regex:/^\s*$/',
        'productos.*.modelo' => 'required_if:productos.*.es_nuevo,true|nullable|string|max:100|not_regex:/^\s*$/',
        'productos.*.color' => 'required_if:productos.*.es_nuevo,true|nullable|string|max:50|not_regex:/^\s*$/',
        'productos.*.cantidad' => 'required|integer|min:1',
        'productos.*.precio_unitario' => 'required|numeric|min:0.01',
        'productos.*.precio_venta' => 'required|numeric|min:0.01',
    ];

    protected $messages = [
        'id_proveedor.required' => 'Debe seleccionar un proveedor.',
        'fecha_compra.required' => 'La fecha es obligatoria.',
        'productos.required' => 'Debe agregar al menos un producto.',
        'productos.min' => 'Debe agregar al menos un producto.',
        'productos.*.id_categoria.required' => 'La categoría es obligatoria para el producto en la fila.',
        'productos.*.nombre.required_if' => 'El nombre es obligatorio para productos nuevos en la fila.',
        'productos.*.nombre.not_regex' => 'El nombre no puede contener solo espacios en la fila.',
        'productos.*.marca.required' => 'La marca es obligatoria para el producto en la fila.',
        'productos.*.marca.not_regex' => 'La marca no puede contener solo espacios en la fila.',
        'productos.*.modelo.required_if' => 'El modelo es obligatorio para productos nuevos en la fila.',
        'productos.*.modelo.not_regex' => 'El modelo no puede contener solo espacios en la fila.',
        'productos.*.color.required_if' => 'El color es obligatorio para productos nuevos en la fila.',
        'productos.*.color.not_regex' => 'El color no puede contener solo espacios en la fila.',
        'productos.*.cantidad.required' => 'La cantidad es obligatoria para el producto en la fila.',
        'productos.*.cantidad.integer' => 'La cantidad debe ser un número entero en la fila.',
        'productos.*.cantidad.min' => 'La cantidad debe ser al menos 1 en la fila.',
        'productos.*.precio_unitario.required' => 'El precio de compra es obligatorio para el producto en la fila.',
        'productos.*.precio_unitario.min' => 'El precio de compra debe ser mayor a 0 en la fila.',
        'productos.*.precio_venta.required' => 'El precio de venta es obligatorio para el producto en la fila.',
        'productos.*.precio_venta.min' => 'El precio de venta debe ser mayor a 0 en la fila.',
    ];

    // Inicializa los valores por defecto al cargar el componente
    public function mount()
    {
        $this->fecha_compra = Carbon::now()->format('Y-m-d');
        $this->numero_compra = $this->generarNumeroCompra();
        $this->buscarProveedores();
        $this->buscarCategorias();
    }

    // Renderiza la vista del componente
    public function render()
    {
        return view('livewire.compras');
    }

    // Verifica si hay cambios no guardados en el formulario
    #[On('check-unsaved-changes')]
    public function checkUnsavedChanges()
    {
        $hasChanges = !empty($this->productos) ||
                      !empty($this->id_proveedor) ||
                      !empty($this->descripcion) ||
                      !empty($this->searchTermProveedor) ||
                      !empty($this->searchTermCategoria) ||
                      !empty($this->searchTermProducto) ||
                      !empty($this->categoria_id) ||
                      !empty($this->producto_id);

        $this->dispatch('has-unsaved-changes', hasChanges: $hasChanges);
    }

    // Genera un número único para la compra
    private function generarNumeroCompra()
    {
        $ultimaCompra = Compra::latest('id')->first();
        $numero = $ultimaCompra ? intval($ultimaCompra->id) + 1 : 1;
        return 'CMP' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    // Busca proveedores según el término de búsqueda
    public function buscarProveedores($term = null)
    {
        $term = $term ?? $this->searchTermProveedor;
        $this->proveedoresFiltrados = Proveedor::query()
            ->where('nombre', 'like', "%{$term}%")
            ->orWhere('razon_social', 'like', "%{$term}%")
            ->orWhere('ruc', 'like', "%{$term}%")
            ->orWhere('telefono', 'like', "%{$term}%")
            ->orderBy('nombre')
            ->take(10)
            ->get();
    }

    // Busca categorías según el término de búsqueda
    public function buscarCategorias($term = null)
    {
        $term = $term ?? $this->searchTermCategoria;
        $this->categoriasFiltradas = Categoria::where('nombre', 'like', "%{$term}%")
            ->orderBy('nombre')
            ->take(10)
            ->get();
    }

    // Busca productos según la categoría seleccionada y el término de búsqueda
    public function buscarProductos($term = null)
    {
        if (!$this->categoria_id) {
            $this->productosFiltrados = [];
            return;
        }

        $term = $term ?? $this->searchTermProducto;
        $this->productosFiltrados = Producto::where('id_categoria', $this->categoria_id)
            ->where(function ($query) use ($term) {
                $query->where('nombre', 'like', "%{$term}%")
                    ->orWhere('marca', 'like', "%{$term}%")
                    ->orWhere('modelo', 'like', "%{$term}%")
                    ->orWhere('color', 'like', "%{$term}%");
            })
            ->orderBy('nombre')
            ->take(10)
            ->get();
    }

    // Actualiza la búsqueda de proveedores y valida la selección
    public function updatedSearchTermProveedor($value)
    {
        $this->buscarProveedores($value);
        $proveedor = Proveedor::where('nombre', $value)
            ->orWhere('razon_social', $value)
            ->first();
        $this->id_proveedor = $proveedor ? $proveedor->id : null;
        $this->resetValidation('id_proveedor');
    }

    // Actualiza la búsqueda de categorías y valida la selección
    public function updatedSearchTermCategoria($value)
    {
        $this->buscarCategorias($value);
        $categoria = Categoria::where('nombre', $value)->first();
        $this->categoria_id = $categoria ? $categoria->id : null;
        $this->searchTermProducto = '';
        $this->producto_id = null;
        $this->buscarProductos();
        $this->resetValidation('categoria_id');
    }

    // Actualiza la búsqueda de productos y valida la selección
    public function updatedSearchTermProducto($value)
    {
        $this->buscarProductos($value);
        $producto = Producto::where('nombre', $value)
            ->where('id_categoria', $this->categoria_id)
            ->first();
        $this->producto_id = $producto ? $producto->id : null;
        $this->resetValidation('producto_id');
    }

    // Actualiza la búsqueda de productos al cambiar la categoría
    public function updatedCategoriaId($value)
    {
        $this->searchTermProducto = '';
        $this->producto_id = null;
        $this->buscarProductos();
    }

    // Abre el modal para crear un nuevo proveedor
    public function openProveedorModal()
    {
        $this->resetValidation();
        $this->nuevoProveedor = [
            'nombre' => '',
            'razon_social' => '',
            'contacto' => '',
            'telefono' => '',
            'email' => '',
            'ruc' => '',
            'direccion' => ''
        ];
        $this->showProveedorModal = true;
    }

    // Cierra el modal de proveedor
    public function closeProveedorModal()
    {
        $this->showProveedorModal = false;
    }

    // Abre el modal para crear una nueva categoría
    public function openCategoriaModal()
    {
        $this->resetValidation();
        $this->nuevaCategoria = [
            'nombre' => '',
            'descripcion' => ''
        ];
        $this->showCategoriaModal = true;
    }

    // Cierra el modal de categoría
    public function closeCategoriaModal()
    {
        $this->showCategoriaModal = false;
    }

    // Guarda un nuevo proveedor en la base de datos
    public function guardarProveedor()
    {
        $this->validate([
            'nuevoProveedor.nombre' => 'required|string|max:255|not_regex:/^\s*$/',
            'nuevoProveedor.razon_social' => 'required|string|max:255|not_regex:/^\s*$/',
            'nuevoProveedor.contacto' => 'required|string|max:255|not_regex:/^\s*$/',
            'nuevoProveedor.telefono' => 'required|string|max:20|regex:/^\d{8}$/',
            'nuevoProveedor.email' => 'required|email|max:255',
            'nuevoProveedor.ruc' => 'required|string|max:14|regex:/^\d+$/',
            'nuevoProveedor.direccion' => 'required|string|max:500|not_regex:/^\s*$/',
        ], [
            'nuevoProveedor.nombre.required' => 'El nombre del proveedor es obligatorio.',
            'nuevoProveedor.nombre.not_regex' => 'El nombre del proveedor no puede contener solo espacios.',
            'nuevoProveedor.razon_social.required' => 'La razón social es obligatoria.',
            'nuevoProveedor.razon_social.not_regex' => 'La razón social no puede contener solo espacios.',
            'nuevoProveedor.contacto.required' => 'La persona de contacto es obligatoria.',
            'nuevoProveedor.contacto.not_regex' => 'La persona de contacto no puede contener solo espacios.',
            'nuevoProveedor.telefono.required' => 'El teléfono del proveedor es obligatorio.',
            'nuevoProveedor.telefono.regex' => 'El teléfono debe contener 8 dígitos.',
            'nuevoProveedor.email.required' => 'El email del proveedor es obligatorio.',
            'nuevoProveedor.email.email' => 'El email debe ser válido.',
            'nuevoProveedor.ruc.required' => 'El RUC es obligatorio.',
            'nuevoProveedor.ruc.regex' => 'El RUC debe contener solo dígitos.',
            'nuevoProveedor.direccion.required' => 'La dirección es obligatoria.',
            'nuevoProveedor.direccion.not_regex' => 'La dirección no puede contener solo espacios.',
        ]);

        try {
            $idTemporalidad = $this->obtenerIdTemporalidadActual();

            $proveedor = Proveedor::create([
                'nombre' => $this->nuevoProveedor['nombre'],
                'razon_social' => $this->nuevoProveedor['razon_social'],
                'contacto' => $this->nuevoProveedor['contacto'],
                'telefono' => $this->nuevoProveedor['telefono'],
                'email' => $this->nuevoProveedor['email'],
                'ruc' => $this->nuevoProveedor['ruc'],
                'direccion' => $this->nuevoProveedor['direccion'],
                'fecha_registro' => Carbon::now(),
                'id_temporalidad' => $idTemporalidad
            ]);

            $this->buscarProveedores();
            $this->id_proveedor = $proveedor->id;
            $this->searchTermProveedor = $proveedor->nombre;
            $this->closeProveedorModal();
        } catch (\Exception $e) {
            Log::error('Error al crear proveedor: ' . $e->getMessage());
        }
    }

   public function guardarCategoria()
{
    $this->validate([
        'nuevaCategoria.nombre' => [
            'required',
            'string',
            'max:255',
            'not_regex:/^\s*$/',
            'unique:categorias,nombre', // Validar que el nombre sea único en la tabla categorias
        ],
        'nuevaCategoria.descripcion' => 'required|string|max:500|not_regex:/^\s*$/',
    ], [
        'nuevaCategoria.nombre.required' => 'El nombre de la categoría es obligatorio.',
        'nuevaCategoria.nombre.not_regex' => 'El nombre de la categoría no puede contener solo espacios.',
        'nuevaCategoria.nombre.unique' => 'Ya existe una categoría con este nombre.',
        'nuevaCategoria.descripcion.required' => 'La descripción de la categoría es obligatoria.',
        'nuevaCategoria.descripcion.not_regex' => 'La descripción de la categoría no puede contener solo espacios.',
    ]);

    try {
        $idTemporalidad = $this->obtenerIdTemporalidadActual();

        $categoria = Categoria::create([
            'nombre' => $this->nuevaCategoria['nombre'],
            'descripcion' => $this->nuevaCategoria['descripcion'],
            'id_temporalidad' => $idTemporalidad,
        ]);

        $this->buscarCategorias();
        $this->categoria_id = $categoria->id;
        $this->searchTermCategoria = $categoria->nombre;
        $this->closeCategoriaModal();
        session()->flash('message', 'Categoría creada correctamente.');
    } catch (\Exception $e) {
        Log::error('Error al crear categoría: ' . $e->getMessage());
        session()->flash('error', 'Error al crear la categoría: ' . $e->getMessage());
    }
}

    // Agrega un producto existente a la lista de productos
    public function agregarProductoExistente()
    {
        $producto = Producto::find($this->producto_id);
        if ($producto) {
            if (empty($producto->marca) || preg_match('/^\s*$/', $producto->marca)) {
                return;
            }
            if (empty($producto->id_categoria)) {
                return;
            }

            $this->productos[] = [
                'id_producto' => $producto->id,
                'id_categoria' => $producto->id_categoria,
                'categoria_nombre' => $producto->categoria->nombre ?? 'Sin categoría',
                'nombre' => $producto->nombre,
                'marca' => $producto->marca,
                'modelo' => $producto->modelo ?? '',
                'color' => $producto->color ?? '',
                'cantidad' => 1,
                'precio_venta' => $producto->precio_venta ?? 0,
                'subtotal' => 0,
                'es_nuevo' => false,
            ];
            $this->calcularSubtotal(count($this->productos) - 1);
            $this->limpiarSearch();
        }
    }

    // Agrega un nuevo producto a la lista de productos
    public function agregarProductoNuevo()
    {
        $this->productos[] = [
            'id_producto' => null,
            'id_categoria' => null,
            'categoria_nombre' => null,
            'nombre' => '',
            'marca' => '',
            'modelo' => '',
            'color' => '',
            'cantidad' => 1,
            'precio_unitario' => 0,
            'precio_venta' => 0,
            'subtotal' => 0,
            'es_nuevo' => true,
        ];

        $this->limpiarSearch();
    }

    // Elimina un producto de la lista
    public function eliminarProducto($index)
    {
        unset($this->productos[$index]);
        $this->productos = array_values($this->productos);
        $this->calcularTotal();
    }

    // Calcula el subtotal de un producto específico
    public function calcularSubtotal($index)
    {
        if (isset($this->productos[$index])) {
            $producto = $this->productos[$index];
            $this->productos[$index]['subtotal'] = floatval($producto['cantidad'] ?? 0) * floatval($producto['precio_unitario'] ?? 0);
            $this->calcularTotal();
        }
    }

    // Calcula el total de la compra sumando los subtotales
    public function calcularTotal()
    {
        $this->total = collect($this->productos)->sum('subtotal');
    }

    // Obtiene o crea un ID de temporalidad para una fecha específica
    private function obtenerIdTemporalidad($fecha)
    {
        try {
            $carbonFecha = Carbon::parse($fecha)->startOfDay();
            $temporalidad = Temporalidad::firstOrCreate(
                ['fecha_completa' => $carbonFecha->toDateString()],
                [
                    'dia_semana' => $carbonFecha->isoFormat('dddd'),
                    'dia_mes' => $carbonFecha->day,
                    'semana_mes' => $carbonFecha->weekOfMonth,
                    'dia_anio' => $carbonFecha->dayOfYear,
                    'semana_anio' => $carbonFecha->weekOfYear,
                    'trimestre_anio' => $carbonFecha->quarter,
                    'mes_anio' => $carbonFecha->month,
                    'vispera_festivo' => false,
                    'anio' => $carbonFecha->year,
                ]
            );
            return $temporalidad->id;
        } catch (\Exception $e) {
            Log::error("Error obteniendo/creando temporalidad para fecha {$fecha}: " . $e->getMessage());
            throw new \Exception("Error interno al procesar la temporalidad de la fecha.", 0, $e);
        }
    }

    // Obtiene el ID de temporalidad para la fecha actual
    private function obtenerIdTemporalidadActual()
    {
        return $this->obtenerIdTemporalidad(Carbon::now()->toDateString());
    }

    // Genera un PDF para la compra registrada
    private function generarPdfCompra($compra)
    {
        try {
            $proveedor = Proveedor::find($compra->id_proveedor);
            $pdfFileName = 'compra_' . $compra->id . '_' . Str::random(10) . '.pdf';
            $pdfPath = 'compras/' . $pdfFileName;
            $fullPath = storage_path('app/public/' . $pdfPath);

            $pdf = Pdf::loadView('pdfs.compra', [
                'compra' => $compra,
                'proveedor' => $proveedor,
                'productos' => $this->productos,
            ]);

            Storage::disk('public')->put($pdfPath, $pdf->output());

            if (!Storage::disk('public')->exists($pdfPath)) {
                Log::error('PDF no encontrado en: ' . $pdfPath);
                throw new \Exception('El PDF no se generó correctamente.');
            }

            return $fullPath;
        } catch (\Exception $e) {
            Log::error('Error al generar PDF: ' . $e->getMessage());
            throw $e;
        }
    }

   public function guardarCompra()
    {
        try {
            Log::info('Iniciando guardarCompra', [
                'id_proveedor' => $this->id_proveedor,
                'fecha_compra' => $this->fecha_compra,
                'total' => $this->total,
                'productos_count' => count($this->productos),
                'productos' => $this->productos,
                'session' => session()->all(),
            ]);

            // Validar las reglas básicas
            $this->validate();

            // Validación personalizada: precio_venta debe ser mayor que precio_unitario
            foreach ($this->productos as $index => $producto) {
                if (floatval($producto['precio_unitario']) >= floatval($producto['precio_venta'])) {
                    Log::warning('Validación de precios fallida', [
                        'index' => $index,
                        'precio_unitario' => $producto['precio_unitario'],
                        'precio_venta' => $producto['precio_venta'],
                    ]);
                    $this->addError("productos.$index.precio_venta", 
                        "El precio de venta debe ser mayor al precio de compra en la fila " . ($index + 1) . ".");
                    session()->flash('error', 'El precio de venta debe ser mayor al precio de compra para todos los productos.');
                    return;
                }
            }

            Log::info('Validación pasada en guardarCompra');

            $idEmpleado = session('empleado_id');
            if (!$idEmpleado) {
                $usuario = session('usuario');
                if ($usuario) {
                    $empleado = Empleado::where('usuario', $usuario)->first();
                    $idEmpleado = $empleado ? $empleado->id : null;
                }
            }

            if (!$idEmpleado) {
                Log::error('No se encontró empleado_id en la sesión');
                session()->flash('error', 'No se pudo identificar al empleado.');
                return;
            }

            $empleado = Empleado::find($idEmpleado);
            if (!$empleado) {
                Log::error('Empleado no encontrado', ['idEmpleado' => $idEmpleado]);
                session()->flash('error', 'El empleado no existe.');
                return;
            }

            DB::beginTransaction();
            Log::info('Iniciando transacción para guardar compra');

            $idTemporalidadCompra = $this->obtenerIdTemporalidad($this->fecha_compra);
            if (!Temporalidad::find($idTemporalidadCompra)) {
                Log::error('Temporalidad no encontrada', ['id_temporalidad' => $idTemporalidadCompra]);
                throw new \Exception('Temporalidad no encontrada: ' . $idTemporalidadCompra);
            }

            Log::info('Datos para Compra::create', [
                'id_proveedor' => $this->id_proveedor,
                'id_empleado' => $idEmpleado,
                'fecha_compra' => $this->fecha_compra,
                'total' => $this->total,
                'descripcion' => $this->descripcion,
                'id_temporalidad' => $idTemporalidadCompra,
            ]);

            $compra = Compra::create([
                'id_proveedor' => $this->id_proveedor,
                'id_empleado' => $idEmpleado,
                'fecha_compra' => $this->fecha_compra,
                'total' => $this->total ?: 0,
                'descripcion' => $this->descripcion,
                'id_temporalidad' => $idTemporalidadCompra,
            ]);
            Log::info('Compra creada', ['compra_id' => $compra->id]);

            $this->numero_compra = 'CMP' . str_pad($compra->id, 6, '0', STR_PAD_LEFT);

            foreach ($this->productos as $index => $item) {
                $idTemporalidadDetalle = $this->obtenerIdTemporalidad($this->fecha_compra);
                $productoId = $item['id_producto'] ?? null;

                if ($item['es_nuevo']) {
                    $idTemporalidadProducto = $this->obtenerIdTemporalidadActual();
                    $nuevoProducto = Producto::create([
                        'nombre' => $item['nombre'],
                        'id_categoria' => $item['id_categoria'],
                        'marca' => $item['marca'],
                        'modelo' => $item['modelo'],
                        'color' => $item['color'],
                        'precio_venta' => $item['precio_venta'],
                        'stock' => 0,
                        'id_temporalidad' => $idTemporalidadProducto,
                        'estado' => 'activo',
                    ]);
                    $productoId = $nuevoProducto->id;
                    Log::info('Producto nuevo creado', [
                        'producto_id' => $productoId,
                        'nombre' => $item['nombre'],
                        'categoria_id' => $item['id_categoria'],
                    ]);
                } else {
                    $productoExistente = Producto::find($productoId);
                    if ($productoExistente) {
                        $productoExistente->update([
                            'precio_venta' => $item['precio_venta'],
                            'marca' => $item['marca'],
                        ]);
                        Log::info('Producto existente actualizado', [
                            'producto_id' => $productoId,
                            'precio_venta' => $item['precio_venta'],
                            'marca' => $item['marca'],
                        ]);
                    } else {
                        Log::error('Producto existente no encontrado', ['producto_id' => $productoId]);
                        throw new \Exception("Producto existente no encontrado en la fila " . ($index + 1));
                    }
                }

                Log::info('Creando detalle de compra', [
                    'id_compra' => $compra->id,
                    'id_producto' => $productoId,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'subtotal' => $item['subtotal'],
                    'id_temporalidad' => $idTemporalidadDetalle,
                ]);

                DetalleCompra::create([
                    'id_compra' => $compra->id,
                    'id_producto' => $productoId,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'subtotal' => $item['subtotal'],
                    'id_temporalidad' => $idTemporalidadDetalle
                ]);
                Log::info('Detalle de compra creado', [
                    'compra_id' => $compra->id,
                    'producto_id' => $productoId,
                ]);

                $idTemporalidadMovimiento = $this->obtenerIdTemporalidadActual();
                MovimientoInventario::create([
                    'id_producto' => $productoId,
                    'id_origen' => $compra->id,
                    'tipo' => 'ENTRADA',
                    'cantidad' => $item['cantidad'],
                    'fecha' => $this->fecha_compra,
                    'descripcion' => "Entrada por compra #{$compra->id}",
                    'id_temporalidad' => $idTemporalidadMovimiento
                ]);
                Log::info('Movimiento de inventario creado', [
                    'producto_id' => $productoId,
                    'compra_id' => $compra->id,
                    'cantidad' => $item['cantidad'],
                ]);

                $producto = Producto::find($productoId);
                if ($producto) {
                    $producto->increment('stock', $item['cantidad']);
                    Log::info('Stock actualizado', [
                        'producto_id' => $productoId,
                        'nuevo_stock' => $producto->stock,
                    ]);
                } else {
                    Log::error('Producto no encontrado para actualizar stock', ['producto_id' => $productoId]);
                    throw new \Exception("Error al actualizar el stock del producto en la fila " . ($index + 1));
                }
            }

            DB::commit();
            Log::info("Compra {$compra->id} registrada por empleado: {$empleado->nombre} {$empleado->apellido} (ID: {$idEmpleado})");

            $pdfPath = null;
            try {
                $pdfPath = $this->generarPdfCompra($compra);
                Log::info('PDF generado', ['pdf_path' => $pdfPath]);
            } catch (\Exception $e) {
                Log::error('Error al generar PDF', ['error' => $e->getMessage()]);
            }

            $this->limpiarCampos();
            session()->flash('message', 'Compra registrada exitosamente.');

            if ($pdfPath) {
                return response()->download($pdfPath, 'comprobante_compra_CMP' . str_pad($compra->id, 6, '0', STR_PAD_LEFT) . '.pdf')->deleteFileAfterSend(true);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validación fallida en guardarCompra', ['errors' => $e->errors()]);
            $this->setErrorBag($e->errors());
            session()->flash('error', 'Por favor, completa todos los campos requeridos correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar compra', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('error', 'Ocurrió un error al registrar la compra: ' . $e->getMessage());
        }
    
    }

    // Limpia los campos de búsqueda de categorías y productos
    public function limpiarSearch()
    {
        $this->reset([
            'searchTermCategoria',
            'searchTermProducto',
            'categoria_id',
            'producto_id',
        ]);

        $this->buscarCategorias();
    }

    // Resetea todos los campos del formulario
    public function limpiarCampos()
    {
        $this->reset([
            'id_proveedor',
            'fecha_compra',
            'descripcion',
            'total',
            'searchTermProveedor',
            'searchTermCategoria',
            'searchTermProducto',
            'categoria_id',
            'producto_id',
            'productos',
            'proveedoresFiltrados',
            'categoriasFiltradas',
            'productosFiltrados',
        ]);
        $this->fecha_compra = Carbon::now()->format('Y-m-d');
        $this->numero_compra = $this->generarNumeroCompra();
        $this->buscarProveedores();
        $this->buscarCategorias();
        $this->resetValidation();
    }
}