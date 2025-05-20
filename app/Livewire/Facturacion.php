<?php

namespace App\Livewire;

use App\Models\Factura;
use App\Models\DetalleFactura;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Temporalidad;
use App\Models\Empleado;
use App\Models\MovimientoInventario;
use App\Models\Categoria;
use App\Models\DatosTarjeta;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;


class Facturacion extends Component
{
    public $proximoFacturaId;
    public $fechaFactura;
    public $searchTermCliente = '';
    public $searchTermCategoria = '';
    public $searchTermProducto = '';
    public $categoria_id;
    public $producto_id;
    public $cliente_id;
    public $codigoProducto = '';
    public $metodo_pago = 'Efectivo';
    public $montoPagado = 0;
    public $cambio = 0;
    public $subtotal = 0;
    public $ivaTotal = 0;
    public $ivaResumen = 0;
    public $showProductoModal = false;
    public $nombre;
    public $marca;
    public $modelo;
    public $color;
    public $precio_venta;
    public $stock;
    public $total = 0;
    public $showModalNuevoCliente = false;
    public $showModalProductoManual = false;
    public $showModalTarjeta = false;
    public $showModalPdf = false;
    public $showCategoriaModal = false;
    public $categoriaId;
    public $nombreCategoria;
    public $descripcionCategoria;
    public $pdfBase64 = '';
    public $pdfErrorMessage = '';
    public $nuevoCliente = [
        'nombre' => '',
        'apellido' => '',
        'telefono' => '',
        'direccion' => ''
    ];
    public $nuevaCategoria = [
        'nombre' => '',
        'descripcion' => ''
    ];
    public $tarjetaNombreTitular = '';
    public $tarjetaNumero = '';
    public $tarjetaFechaExpiracion = '';
    public $tarjetaTipo = 'Debito';
    public $productosEnFactura = [];
    public $clientesFiltrados = [];
    public $categoriasFiltradas = [];
    public $productosFiltrados = [];

    protected $messages = [
        'cliente_id.required' => 'Debe seleccionar un cliente.',
        'fechaFactura.required' => 'La fecha es obligatoria.',
        'productosEnFactura.required' => 'Debe agregar al menos un producto.',
        'productosEnFactura.min' => 'Debe agregar al menos un producto.',
        'productosEnFactura.*.cantidad.required' => 'La cantidad es obligatoria.',
        'productosEnFactura.*.cantidad.min' => 'La cantidad debe ser al menos 1.',
        'productosEnFactura.*.precio_unitario.required' => 'El precio unitario es obligatorio.',
        'productosEnFactura.*.precio_unitario.min' => 'El precio unitario debe ser mayor a 0.',
        'productosEnFactura.*.iva.required' => 'El IVA es obligatorio.',
        'metodo_pago.required' => 'Debe seleccionar un método de pago.',
        'montoPagado.required_if' => 'El monto pagado es obligatorio para pagos en efectivo.',
        'montoPagado.min' => 'El monto pagado debe ser mayor o igual al total.',
        'tarjetaNombreTitular.required_if' => 'El nombre del titular es obligatorio para pagos con tarjeta.',
        'tarjetaNumero.required_if' => 'El número de tarjeta es obligatorio.',
        'tarjetaNumero.regex' => 'El número de tarjeta debe tener 16 dígitos.',
        'tarjetaFechaExpiracion.required_if' => 'La fecha de expiración es obligatoria.',
        'tarjetaFechaExpiracion.after_or_equal' => 'La fecha de expiración debe ser válida.',
        'tarjetaTipo.required_if' => 'El tipo de tarjeta es obligatorio.',
        'tarjetaTipo.in' => 'El tipo de tarjeta debe ser Débito o Crédito.',
        'codigoProducto.required' => 'El ID del producto es obligatorio.',
        'codigoProducto.exists' => 'El ID del producto no existe.',
        'nuevoCliente.nombre.required' => 'El nombre del cliente es obligatorio.',
        'nuevoCliente.nombre.not_regex' => 'El nombre no puede contener solo espacios.',
        'nuevoCliente.apellido.required' => 'El apellido del cliente es obligatorio.',
        'nuevoCliente.apellido.not_regex' => 'El apellido no puede contener solo espacios.',
        'nuevoCliente.telefono.required' => 'El teléfono del cliente es obligatorio.',
        'nuevoCliente.telefono.not_regex' => 'El teléfono no puede contener solo espacios.',
        // Nuevos mensajes añadidos
    'nombre.required' => 'El nombre del producto es obligatorio.',
    'nombre.not_regex' => 'El nombre no puede contener solo espacios.',
    'marca.required' => 'La marca es obligatoria.',
    'marca.not_regex' => 'La marca no puede contener solo espacios.',
    'categoria_id.required' => 'Debe seleccionar una categoría.',
    'categoria_id.exists' => 'La categoría seleccionada no existe.',
    'precio_venta.required' => 'El precio de venta es obligatorio.',
    'precio_venta.numeric' => 'El precio de venta debe ser un número.',
    'stock.required' => 'El stock inicial es obligatorio.',
    'stock.integer' => 'El stock debe ser un número entero.',
    'stock.min' => 'El stock inicial debe ser mayor a 0.',
    ];

    // Inicializa los valores por defecto al cargar el componente
    public function mount()
    {
        Log::info('Prueba de logging desde Facturacion::mount');
        $this->fechaFactura = Carbon::now()->format('Y-m-d');
        $this->proximoFacturaId = $this->generarNumeroFactura();
        $this->buscarClientes();
        $this->buscarCategorias();
        Log::debug('Componente Facturacion montado', ['proximoFacturaId' => $this->proximoFacturaId]);
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
    
     // Resetea los campos del formulario de categoría
    private function resetCategoriaForm()
    {
        $this->categoriaId = null;
        $this->nombreCategoria = '';
        $this->descripcionCategoria = '';
    }
    // Guarda una nueva categoría en la base de datos
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

    // Renderiza la vista del componente
public function render()
{
    return view('livewire.facturacion', [
        'clientesFiltrados' => $this->clientesFiltrados,
        'categoriasFiltradas' => $this->categoriasFiltradas,
        'productosFiltrados' => $this->productosFiltrados,
        'categorias' => Categoria::orderBy('nombre')->get(),
    ]);
}


    // Verifica si hay cambios no guardados en el formulario
    #[On('check-unsaved-changes')]
    public function checkUnsavedChanges()
    {
        $hasChanges = !empty($this->productosEnFactura) ||
                      !empty($this->cliente_id) ||
                      !empty($this->searchTermCliente) ||
                      !empty($this->searchTermCategoria) ||
                      !empty($this->searchTermProducto) ||
                      !empty($this->categoria_id) ||
                      !empty($this->producto_id) ||
                      !empty($this->codigoProducto) ||
                      !empty($this->montoPagado) ||
                      $this->metodo_pago !== 'Efectivo' ||
                      !empty($this->tarjetaNombreTitular) ||
                      !empty($this->tarjetaNumero) ||
                      !empty($this->tarjetaFechaExpiracion) ||
                      $this->tarjetaTipo !== 'Debito';

        $this->dispatch('has-unsaved-changes', hasChanges: $hasChanges);
    }

    // Define las reglas de validación para los campos del formulario
    public function rules()
    {
        return [
            'cliente_id' => 'required|exists:clientes,id',
            'fechaFactura' => 'required|date',
            'productosEnFactura' => 'required|array|min:1',
            'productosEnFactura.*.producto_id' => 'nullable|exists:productos,id',
            'productosEnFactura.*.cantidad' => 'required|numeric|min:1',
            'productosEnFactura.*.precio_unitario' => 'required|numeric|min:0',
            'productosEnFactura.*.iva' => 'required|numeric|min:0',
            'metodo_pago' => 'required|in:Efectivo,Tarjeta',
            'montoPagado' => 'required_if:metodo_pago,Efectivo|numeric|min:' . $this->total,
            'tarjetaNombreTitular' => 'required_if:metodo_pago,Tarjeta|string|max:255',
            'tarjetaNumero' => 'required_if:metodo_pago,Tarjeta|regex:/^\d{4}\s?\d{4}\s?\d{4}\s?\d{4}$/',
            'tarjetaFechaExpiracion' => 'required_if:metodo_pago,Tarjeta|date_format:Y-m|after_or_equal:' . Carbon::now()->format('Y-m'),
            'tarjetaTipo' => 'required_if:metodo_pago,Tarjeta|in:Debito,Credito',
        ];
    }

    // Método para abrir el modal de nuevo producto
public function openProductoModal()
{
    $this->resetValidation();
    $this->resetProductoForm();
    $this->showProductoModal = true;
    Log::debug('Modal de nuevo producto abierto');
}

// Método para cerrar el modal de nuevo producto
public function closeProductoModal()
{
    $this->showProductoModal = false;
    $this->resetProductoForm();
    Log::debug('Modal de nuevo producto cerrado');
}

// Método para resetear el formulario de producto
private function resetProductoForm()
{
    $this->nombre = '';
    $this->marca = '';
    $this->modelo = '';
    $this->color = '';
    $this->categoria_id = '';
    $this->precio_venta = '';
    $this->stock = '';
    $this->resetValidation();
}

// Método para guardar el nuevo producto
public function saveProducto()
{
    $this->validate([
        'nombre' => 'required|string|max:255|not_regex:/^\s*$/',
        'marca' => 'required|string|max:100|not_regex:/^\s*$/',
        'modelo' => 'nullable|string|max:100',
        'color' => 'nullable|string|max:50',
        'categoria_id' => 'required|exists:categorias,id',
        'precio_venta' => 'required|numeric|min:0',
        'stock' => 'required|integer|min:1',
    ]);

    try {
        DB::beginTransaction();

        $temporalidadId = $this->obtenerIdTemporalidadActual();

        $producto = Producto::create([
            'nombre' => $this->nombre,
            'marca' => $this->marca,
            'modelo' => $this->modelo,
            'color' => $this->color,
            'id_categoria' => $this->categoria_id,
            'precio_venta' => $this->precio_venta,
            'stock' => $this->stock,
            'id_temporalidad' => $temporalidadId,
            'estado' => 'activo',
        ]);

        if ($this->stock > 0) {
            $temporalidadMovimientoId = $this->obtenerIdTemporalidadActual();
            MovimientoInventario::create([
                'id_producto' => $producto->id,
                'tipo' => 'entrada',
                'cantidad' => $this->stock,
                'fecha' => now(),
                'descripcion' => 'Stock inicial desde facturación',
                'id_temporalidad' => $temporalidadMovimientoId,
            ]);
        }

        // Agregar el producto a la factura
        $this->productosEnFactura[] = [
            'producto_id' => $producto->id,
            'categoria' => $producto->categoria->nombre ?? 'Sin categoría',
            'nombre_producto' => $producto->nombre,
            'marca_producto' => $producto->marca ?? '',
            'modelo_producto' => $producto->modelo ?? '',
            'color_producto' => $producto->color ?? '',
            'stock' => $producto->stock,
            'cantidad' => 1,
            'precio_unitario' => $producto->precio_venta ?? 0,
            'iva' => 15,
            'subtotal' => $producto->precio_venta ?? 0,
        ];

        $this->calcularTotales();

        DB::commit();
        session()->flash('message', 'Producto creado y agregado a la factura correctamente.');
        Log::debug("Producto creado desde facturación ID: {$producto->id}");
        $this->closeProductoModal();
        $this->resetProductoForm();
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error al guardar producto desde facturación: ' . $e->getMessage());
        session()->flash('error', 'Error al guardar el producto: ' . $e->getMessage());
    }
}
    // Define las reglas de validación para el código del producto
    protected function rulesForCodigoProducto()
    {
        return [
            'codigoProducto' => 'required|exists:productos,id',
        ];
    }

    // Define las reglas de validación para el nuevo cliente
    protected function rulesForNuevoCliente()
    {
        return [
            'nuevoCliente.nombre' => 'required|string|max:255|not_regex:/^\s*$/',
            'nuevoCliente.apellido' => 'required|string|max:255|not_regex:/^\s*$/',
            'nuevoCliente.telefono' => 'required|string|max:20|not_regex:/^\s*$/',
            'nuevoCliente.direccion' => 'nullable|string|max:500',
        ];
    }

    // Define las reglas de validación para la selección manual de productos
    protected function rulesForProductoManual()
    {
        return [
            'categoria_id' => 'required|exists:categorias,id',
            'producto_id' => 'required|exists:productos,id',
        ];
    }

    // Genera un número único para la factura
    private function generarNumeroFactura()
    {
        $ultimaFactura = Factura::latest('id')->first();
        $numero = $ultimaFactura ? intval($ultimaFactura->id) + 1 : 1;
        $numeroFactura = 'FAC' . str_pad($numero, 6, '0', STR_PAD_LEFT);
        Log::debug('Generando número de factura', ['numeroFactura' => $numeroFactura]);
        return $numeroFactura;
    }

    // Busca clientes según el término de búsqueda
    public function buscarClientes($term = null)
    {
        $term = $term ?? $this->searchTermCliente;
        $this->clientesFiltrados = Cliente::query()
            ->whereRaw("CONCAT(nombre, ' ', apellido) LIKE ?", ["%{$term}%"])
            ->orWhere('nombre', 'like', "%{$term}%")
            ->orWhere('apellido', 'like', "%{$term}%")
            ->orWhere('telefono', 'like', "%{$term}%")
            ->orderBy('nombre')
            ->take(10)
            ->get();
        Log::debug('Clientes filtrados', ['term' => $term, 'count' => $this->clientesFiltrados->count()]);
    }

    // Busca categorías según el término de búsqueda
    public function buscarCategorias($term = null)
    {
        $term = $term ?? $this->searchTermCategoria;
        $this->categoriasFiltradas = Categoria::where('nombre', 'like', "%{$term}%")
            ->orderBy('nombre')
            ->take(10)
            ->get();
        Log::debug('Categorías filtradas', ['term' => $term, 'count' => $this->categoriasFiltradas->count()]);
    }

    // Busca productos según la categoría seleccionada y el término de búsqueda
    public function buscarProductos($term = null)
    {
        if (!$this->categoria_id) {
            $this->productosFiltrados = [];
            Log::debug('No se buscaron productos, categoría no seleccionada');
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
        Log::debug('Productos filtrados', ['term' => $term, 'categoria_id' => $this->categoria_id, 'count' => $this->productosFiltrados->count()]);
    }

    // Actualiza la búsqueda de clientes y valida la selección
    public function updatedSearchTermCliente($value)
    {
        Log::debug('updatedSearchTermCliente ejecutado', ['value' => $value]);
        $this->buscarClientes($value);
        $this->cliente_id = null;
        if ($value) {
            $cliente = Cliente::whereRaw("CONCAT(nombre, ' ', apellido) = ?", [$value])
                ->orWhere('telefono', $value)
                ->first();
            if ($cliente) {
                $this->cliente_id = $cliente->id;
                $this->searchTermCliente = $cliente->nombre . ' ' . $cliente->apellido;
                Log::debug('Cliente seleccionado automáticamente', ['cliente_id' => $this->cliente_id]);
            }
        }
        $this->resetValidation('cliente_id');
    }

    // Selecciona un cliente manualmente
    public function selectCliente($clienteId)
    {
        $cliente = Cliente::find($clienteId);
        if ($cliente) {
            $this->cliente_id = $cliente->id;
            $this->searchTermCliente = $cliente->nombre . ' ' . $cliente->apellido;
            Log::debug('Cliente seleccionado manualmente', ['cliente_id' => $this->cliente_id]);
        }
        $this->resetValidation('cliente_id');
    }

    // Actualiza la búsqueda de categorías y valida la selección
    public function updatedSearchTermCategoria($value)
    {
        $this->buscarCategorias($value);
        if ($value) {
            $categoria = Categoria::where('nombre', $value)->first();
            $this->categoria_id = $categoria ? $categoria->id : null;
        } else {
            $this->categoria_id = null;
        }
        $this->searchTermProducto = '';
        $this->producto_id = null;
        $this->buscarProductos();
        $this->resetValidation('categoria_id');
    }

    // Selecciona una categoría manualmente
    public function selectCategoria($categoriaId)
    {
        $categoria = Categoria::find($categoriaId);
        if ($categoria) {
            $this->categoria_id = $categoria->id;
            $this->searchTermCategoria = $categoria->nombre;
        }
        $this->searchTermProducto = '';
        $this->producto_id = null;
        $this->buscarProductos();
        $this->resetValidation('categoria_id');
    }

    // Actualiza la búsqueda de productos y valida la selección
    public function updatedSearchTermProducto($value)
    {
        $this->buscarProductos($value);
        if ($value && $this->categoria_id) {
            $producto = Producto::where('nombre', $value)
                ->where('id_categoria', $this->categoria_id)
                ->first();
            $this->producto_id = $producto ? $producto->id : null;
        } else {
            $this->producto_id = null;
        }
        $this->resetValidation('producto_id');
    }

    // Selecciona un producto manualmente
    public function selectProducto($productoId)
    {
        $producto = Producto::find($productoId);
        if ($producto) {
            $this->producto_id = $producto->id;
            $this->searchTermProducto = $producto->nombre;
        }
        $this->resetValidation('producto_id');
    }

    // Actualiza la búsqueda de productos al cambiar la categoría
    public function updatedCategoriaId($value)
    {
        $this->searchTermProducto = '';
        $this->producto_id = null;
        $this->buscarProductos();
        $this->resetValidation('categoria_id');
    }

    // Recalcula los totales al actualizar los productos en la factura
    public function updatedProductosEnFactura()
    {
        $this->calcularTotales();
    }

    // Actualiza el cambio según el monto pagado
    public function updatedMontoPagado($value)
    {
        if (is_numeric($value) && $value !== '') {
            $this->cambio = floatval($value) - floatval($this->total);
            if ($this->cambio < 0 && $this->metodo_pago == 'Efectivo') {
                $this->addError('montoPagado', 'El monto pagado debe ser mayor o igual al total.');
            } else {
                $this->resetValidation('montoPagado');
            }
        } else {
            $this->cambio = 0;
        }
    }

    // Actualiza los valores relacionados con el método de pago
    public function updatedMetodoPago($value)
    {
        if ($value == 'Tarjeta' && $this->total < 300) {
            $this->metodo_pago = 'Efectivo';
            session()->flash('error', 'El pago con tarjeta no está disponible para montos menores a $300.');
            return;
        }

        if ($value == 'Efectivo') {
            $this->showModalTarjeta = false;
            $this->reset(['tarjetaNombreTitular', 'tarjetaNumero', 'tarjetaFechaExpiracion', 'tarjetaTipo']);
            $this->montoPagado = $this->total;
            $this->cambio = 0;
        } else {
            $this->montoPagado = $this->total;
            $this->cambio = 0;
        }
    }

    // Verifica si el pago con tarjeta está deshabilitado
    public function isTarjetaDisabled()
    {
        return $this->total < 300;
    }

    // Abre el modal para crear un nuevo cliente
    public function abrirModalNuevoCliente()
    {
        $this->resetValidation();
        $this->nuevoCliente = [
            'nombre' => '',
            'apellido' => '',
            'telefono' => '',
            'direccion' => ''
        ];
        $this->showModalNuevoCliente = true;
    }

    // Cierra el modal de nuevo cliente
    public function cerrarModalNuevoCliente()
    {
        $this->showModalNuevoCliente = false;
    }

    // Abre el modal para agregar un producto manualmente
    public function abrirModalProductoManual()
    {
        $this->resetValidation();
        $this->searchTermCategoria = '';
        $this->searchTermProducto = '';
        $this->categoria_id = null;
        $this->producto_id = null;
        $this->buscarCategorias();
        $this->showModalProductoManual = true;
    }

    // Cierra el modal de producto manual
    public function cerrarModalProductoManual()
    {
        $this->showModalProductoManual = false;
    }

    // Abre el modal para ingresar datos de tarjeta
    public function abrirModalTarjeta()
    {
        $this->resetValidation();
        $this->tarjetaNombreTitular = '';
        $this->tarjetaNumero = '';
        $this->tarjetaFechaExpiracion = Carbon::now()->format('Y-m');
        $this->tarjetaTipo = 'Debito';
        $this->showModalTarjeta = true;
    }

    // Cierra el modal de datos de tarjeta
    public function cerrarModalTarjeta()
    {
        $this->showModalTarjeta = false;
    }

    // Abre el modal para visualizar el PDF
    public function abrirModalPdf()
    {
        $this->showModalPdf = true;
    }

    // Cierra el modal de visualización del PDF
    public function cerrarModalPdf()
    {
        $this->showModalPdf = false;
        $this->pdfBase64 = '';
        $this->pdfErrorMessage = '';
        Log::debug('Modal PDF cerrado', [
            'showModalPdf' => $this->showModalPdf,
            'pdfBase64_length' => strlen($this->pdfBase64)
        ]);
    }

    // Guarda un nuevo cliente en la base de datos
    public function guardarNuevoCliente()
    {
        $this->validate($this->rulesForNuevoCliente());

        try {
            $idTemporalidad = $this->obtenerIdTemporalidadActual();

            $cliente = Cliente::create([
                'nombre' => $this->nuevoCliente['nombre'],
                'apellido' => $this->nuevoCliente['apellido'],
                'telefono' => $this->nuevoCliente['telefono'],
                'direccion' => empty(trim($this->nuevoCliente['direccion'])) ? 'Opcional' : $this->nuevoCliente['direccion'],
                'fecha_registro' => Carbon::now(),
                'id_temporalidad' => $idTemporalidad
            ]);

            $this->buscarClientes();
            $this->cliente_id = $cliente->id;
            $this->searchTermCliente = $cliente->nombre . ' ' . $cliente->apellido;
            $this->cerrarModalNuevoCliente();
            session()->flash('message', 'Cliente creado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al crear cliente: ' . $e->getMessage());
            session()->flash('error', 'Error al crear el cliente.');
        }
    }

    // Agrega un producto a la factura usando su código
    public function agregarPorCodigo()
    {
        $this->validate($this->rulesForCodigoProducto());

        $producto = Producto::with('categoria')->find($this->codigoProducto);

        if (!$producto) {
            session()->flash('error', 'Producto no encontrado.');
            $this->reset(['codigoProducto']);
            return;
        }

        if ($producto->stock <= 0) {
            session()->flash('error', "El producto '{$producto->nombre}' no tiene stock disponible.");
            $this->reset(['codigoProducto']);
            return;
        }

        $existingIndex = collect($this->productosEnFactura)->search(function ($item) use ($producto) {
            return $item['producto_id'] == $producto->id;
        });

        if ($existingIndex !== false) {
            $this->productosEnFactura[$existingIndex]['cantidad']++;
            if ($this->productosEnFactura[$existingIndex]['cantidad'] > $producto->stock) {
                $this->productosEnFactura[$existingIndex]['cantidad'] = $producto->stock;
                session()->flash('warning', "No hay más stock disponible para '{$producto->nombre}'.");
            }
        } else {
            $this->productosEnFactura[] = [
                'producto_id' => $producto->id,
                'categoria' => $producto->categoria->nombre ?? 'Sin categoría',
                'nombre_producto' => $producto->nombre,
                'marca_producto' => $producto->marca ?? '',
                'modelo_producto' => $producto->modelo ?? '',
                'color_producto' => $producto->color ?? '',
                'stock' => $producto->stock,
                'cantidad' => 1,
                'precio_unitario' => $producto->precio_venta ?? 0,
                'iva' => 15,
                'subtotal' => $producto->precio_venta ?? 0,
            ];
        }

        $this->calcularTotales();
        session()->flash('message', "Producto '{$producto->nombre}' agregado.");
        $this->reset(['codigoProducto']);
        $this->resetValidation('codigoProducto');
    }

    // Agrega un producto a la factura manualmente
    public function agregarProductoManual()
    {
        $this->validate($this->rulesForProductoManual());

        $producto = Producto::find($this->producto_id);
        if (!$producto) {
            $this->addError('producto_id', 'Producto no encontrado.');
            return;
        }

        if ($producto->stock < 1) {
            $this->addError('producto_id', 'No hay suficiente stock disponible.');
            return;
        }

        $existingIndex = collect($this->productosEnFactura)->search(function ($item) use ($producto) {
            return $item['producto_id'] == $producto->id;
        });

        if ($existingIndex !== false) {
            $this->productosEnFactura[$existingIndex]['cantidad']++;
            if ($this->productosEnFactura[$existingIndex]['cantidad'] > $producto->stock) {
                $this->productosEnFactura[$existingIndex]['cantidad'] = $producto->stock;
                session()->flash('warning', "No hay más stock disponible para '{$producto->nombre}'.");
            }
        } else {
            $this->productosEnFactura[] = [
                'producto_id' => $producto->id,
                'nombre_producto' => $producto->nombre,
                'marca_producto' => $producto->marca ?? '',
                'modelo_producto' => $producto->modelo ?? '',
                'color_producto' => $producto->color ?? '',
                'stock' => $producto->stock,
                'cantidad' => 1,
                'precio_unitario' => $producto->precio_venta ?? 0,
                'iva' => 15,
                'subtotal' => 0,
            ];
        }

        $this->calcularTotales();
        $this->cerrarModalProductoManual();
    }

    // Elimina un producto de la factura
    public function eliminarProducto($index)
    {
        unset($this->productosEnFactura[$index]);
        $this->productosEnFactura = array_values($this->productosEnFactura);
        $this->calcularTotales();
    }

    // Calcula los subtotales, IVA y total de la factura
    public function calcularTotales()
    {
        $this->subtotal = 0;
        $this->ivaTotal = 0;
        $this->total = 0;

        foreach ($this->productosEnFactura as $index => $item) {
            $subtotalItem = floatval($item['cantidad'] ?? 0) * floatval($item['precio_unitario'] ?? 0);
            $ivaAmount = $subtotalItem * (floatval($item['iva'] ?? 0) / 100);
            $this->productosEnFactura[$index]['subtotal'] = $subtotalItem + $ivaAmount;
            $this->subtotal += $subtotalItem;
            $this->ivaTotal += $ivaAmount;
        }

        $this->total = $this->subtotal + $this->ivaTotal;
        $this->ivaResumen = count($this->productosEnFactura) > 0 ? collect($this->productosEnFactura)->average('iva') : 0;

        if ($this->metodo_pago == 'Efectivo') {
            $this->cambio = floatval($this->montoPagado) - floatval($this->total);
        } else {
            $this->montoPagado = $this->total;
            $this->cambio = 0;
        }
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
            Log::error("Error obteniendo temporalidad: {$e->getMessage()}");
            throw new \Exception("Error al procesar la temporalidad.", 0, $e);
        }
    }

    // Obtiene el ID de temporalidad para la fecha actual
    private function obtenerIdTemporalidadActual()
    {
        return $this->obtenerIdTemporalidad(Carbon::now()->toDateString());
    }

    // Genera un PDF para la factura registrada
    private function generarPdfFactura($factura)
    {
        try {
            Log::info('Iniciando generación de PDF', ['factura_id' => $factura->id ?? 'N/A']);

            if (!$factura || !isset($factura->id)) {
                Log::error('Factura no proporcionada o inválida', ['factura' => $factura]);
                $this->pdfErrorMessage = 'Factura no encontrada.';
                return '';
            }
            Log::debug('Factura validada', ['factura_id' => $factura->id]);

            $cliente = Cliente::find($factura->id_cliente);
            if (!$cliente) {
                Log::error('Cliente no encontrado', ['id_cliente' => $factura->id_cliente]);
                $this->pdfErrorMessage = 'Cliente no encontrado.';
                return '';
            }
            Log::debug('Cliente validado', ['cliente_id' => $cliente->id]);

            if (empty($this->productosEnFactura)) {
                Log::error('No hay productos en la factura', ['productosEnFactura' => $this->productosEnFactura]);
                $this->pdfErrorMessage = 'No hay productos para generar el PDF.';
                return '';
            }
            Log::debug('Productos presentes', ['producto_count' => count($this->productosEnFactura)]);

            $productosNormalizados = array_map(function ($producto) {
                $normalized = [
                    'nombre_producto' => $producto['nombre_producto'] ?? 'Producto desconocido',
                    'cantidad' => $producto['cantidad'] ?? 1,
                    'precio_unitario' => $producto['precio_unitario'] ?? 0,
                    'subtotal' => $producto['subtotal'] ?? 0,
                    'iva' => $producto['iva'] ?? 0,
                    'marca_producto' => $producto['marca_producto'] ?? '',
                    'modelo_producto' => $producto['modelo_producto'] ?? '',
                    'color_producto' => $producto['color_producto'] ?? '',
                ];
                Log::debug('Producto normalizado', ['producto' => $normalized]);
                return $normalized;
            }, $this->productosEnFactura);
            Log::debug('Todos los productos normalizados', ['productos' => $productosNormalizados]);

            $vistaPath = resource_path('views/pdfs/facturas/ticket.blade.php');
            if (!File::exists($vistaPath)) {
                Log::error('La vista ticket.blade.php no existe', ['path' => $vistaPath]);
                $this->pdfErrorMessage = 'La vista del ticket no se encuentra.';
                return '';
            }
            Log::debug('Vista ticket.blade.php encontrada', ['path' => $vistaPath]);

            ini_set('memory_limit', '512M');
            $options = [
                'isRemoteEnabled' => false,
                'isHtml5ParserEnabled' => true,
                'dpi' => 96,
                'defaultFont' => 'Helvetica',
                'isFontSubsettingEnabled' => false,
            ];
            Log::debug('Configuración de DomPDF establecida', ['options' => $options]);

            $vistaData = [
                'factura' => $factura,
                'cliente' => $cliente,
                'productos' => $productosNormalizados,
                'subtotal' => $this->subtotal ?? 0,
                'ivaTotal' => $this->ivaTotal ?? 0,
            ];
            Log::debug('Datos enviados a la vista', ['data' => $vistaData]);

            $pdf = Pdf::setOptions($options)
                ->loadView('pdfs.facturas.ticket', $vistaData)
                ->setPaper([0, 0, 227, 1000], 'portrait');
            Log::debug('Vista ticket.blade.php cargada en DomPDF');

            $pdfOutput = $pdf->output();
            if (empty($pdfOutput)) {
                Log::error('El output del PDF está vacío', ['vistaData' => $vistaData]);
                $this->pdfErrorMessage = 'No se pudo generar el contenido del PDF.';
                return '';
            }
            Log::debug('PDF output generado', ['output_length' => strlen($pdfOutput)]);

            $pdfBase64 = base64_encode($pdfOutput);
            Log::info('PDF codificado en base64', [
                'factura_id' => $factura->id,
                'base64_length' => strlen($pdfBase64),
                'base64_preview' => substr($pdfBase64, 0, 100)
            ]);

            ini_set('memory_limit', '128M');

            $this->pdfErrorMessage = '';
            return $pdfBase64;
        } catch (\Exception $e) {
            Log::error('Error al generar PDF: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'factura_id' => $factura->id ?? 'N/A',
                'productos' => $this->productosEnFactura,
            ]);
            $this->pdfErrorMessage = 'Error al generar el PDF: ' . $e->getMessage();
            return '';
        }
    }

    // Procesa la factura, registra los detalles y actualiza el inventario
    public function procesarFactura()
    {
        try {
            Log::info('Iniciando procesarFactura', [
                'cliente_id' => $this->cliente_id,
                'fechaFactura' => $this->fechaFactura,
                'total' => $this->total,
                'metodo_pago' => $this->metodo_pago,
                'producto_count' => count($this->productosEnFactura),
            ]);

            if ($this->metodo_pago == 'Tarjeta' && $this->total < 300) {
                session()->flash('error', 'El pago con tarjeta no está disponible para montos menores a $300.');
                $this->metodo_pago = 'Efectivo';
                return;
            }

            if ($this->metodo_pago == 'Tarjeta' && !$this->showModalTarjeta) {
                $this->abrirModalTarjeta();
                return;
            }

            $this->validate();
            Log::info('Validación pasada');

            $idEmpleado = session('empleado_id');
            if (!$idEmpleado) {
                $usuario = session('usuario');
                if ($usuario) {
                    $empleado = Empleado::where('usuario', $usuario)->first();
                    $idEmpleado = $empleado ? $empleado->id : null;
                }
            }

            if (!$idEmpleado) {
                Log::warning('No se encontró empleado_id');
                session()->flash('error', 'Debes iniciar sesión como empleado.');
                return;
            }

            $empleado = Empleado::find($idEmpleado);
            if (!$empleado) {
                Log::error('Empleado no encontrado', ['idEmpleado' => $idEmpleado]);
                session()->flash('error', 'El empleado no es válido.');
                return;
            }

            foreach ($this->productosEnFactura as $index => $item) {
                if ($item['producto_id']) {
                    $producto = Producto::find($item['producto_id']);
                    if ($producto && $producto->stock < $item['cantidad']) {
                        $this->addError("productosEnFactura.{$index}.cantidad", 'No hay suficiente stock para: ' . $item['nombre_producto']);
                        return;
                    }
                }
            }

            DB::beginTransaction();

            $idTemporalidadFactura = $this->obtenerIdTemporalidad($this->fechaFactura);

            $factura = Factura::create([
                'id_cliente' => $this->cliente_id,
                'id_empleado' => $idEmpleado,
                'fecha_factura' => $this->fechaFactura,
                'total' => $this->total,
                'iva' => $this->ivaTotal,
                'totalcancelado' => $this->montoPagado,
                'cambio' => $this->cambio,
                'metodo_pago' => $this->metodo_pago,
                'id_temporalidad' => $idTemporalidadFactura,
            ]);

            $this->proximoFacturaId = 'FAC' . str_pad($factura->id, 6, '0', STR_PAD_LEFT);
            Log::info('Factura creada', ['factura_id' => $factura->id]);

            if ($this->metodo_pago == 'Tarjeta') {
                DatosTarjeta::create([
                    'id_factura' => $factura->id,
                    'nombre_titular' => $this->tarjetaNombreTitular,
                    'numero_tarjeta' => $this->tarjetaNumero,
                    'fecha_expiracion' => $this->tarjetaFechaExpiracion,
                    'tipo_tarjeta' => $this->tarjetaTipo,
                ]);
                Log::info('Datos de tarjeta guardados', ['factura_id' => $factura->id]);
            }

            foreach ($this->productosEnFactura as $item) {
                $idTemporalidadDetalle = $this->obtenerIdTemporalidad($this->fechaFactura);

                DetalleFactura::create([
                    'id_factura' => $factura->id,
                    'id_producto' => $item['producto_id'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'iva' => $item['iva'],
                    'subtotal' => $item['subtotal'],
                    'id_temporalidad' => $idTemporalidadDetalle,
                ]);

                $producto = Producto::find($item['producto_id']);
                if ($producto) {
                    $producto->decrement('stock', $item['cantidad']);
                    $idTemporalidadMovimiento = $this->obtenerIdTemporalidadActual();
                    MovimientoInventario::create([
                        'id_producto' => $item['producto_id'],
                        'id_origen' => $factura->id,
                        'tipo' => 'SALIDA',
                        'cantidad' => $item['cantidad'],
                        'fecha' => $this->fechaFactura,
                        'descripcion' => "Salida por factura #{$this->proximoFacturaId}",
                        'id_temporalidad' => $idTemporalidadMovimiento,
                    ]);
                }
            }

            $this->pdfBase64 = $this->generarPdfFactura($factura);
            Log::info('Resultado de generarPdfFactura', [
                'factura_id' => $factura->id,
                'pdfBase64_length' => strlen($this->pdfBase64),
                'pdfBase64_preview' => substr($this->pdfBase64, 0, 100),
                'pdfErrorMessage' => $this->pdfErrorMessage
            ]);

            if (empty($this->pdfBase64)) {
                Log::error('pdfBase64 está vacío antes de abrir el modal', [
                    'factura_id' => $factura->id,
                    'pdfErrorMessage' => $this->pdfErrorMessage
                ]);
                session()->flash('error', $this->pdfErrorMessage ?: 'No se pudo generar el PDF.');
            } else {
                Log::info('pdfBase64 generado, abriendo modal', [
                    'factura_id' => $factura->id,
                    'base64_length' => strlen($this->pdfBase64)
                ]);
            }
            if (empty($this->productosEnFactura)) {
                Log::error('No hay productos en la factura antes de generar PDF', ['factura_id' => $factura->id]);
                session()->flash('error', 'No hay productos para generar la factura.');
                DB::rollBack();
                return;
            }
            if (!$this->cliente_id || !Cliente::find($this->cliente_id)) {
                Log::error('Cliente inválido antes de generar PDF', ['cliente_id' => $this->cliente_id]);
                session()->flash('error', 'Cliente no válido.');
                DB::rollBack();
                return;
            }

            DB::commit();
            session()->flash('message', 'Factura registrada correctamente.');
            $this->abrirModalPdf();

            if ($this->metodo_pago == 'Tarjeta') {
                $this->cerrarModalTarjeta();
            }
            $this->limpiarCampos();
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validación fallida: ' . json_encode($e->errors()));
            $this->setErrorBag($e->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar factura: ' . $e->getMessage());
            session()->flash('error', 'Error al registrar la factura: ' . $e->getMessage());
        }
    }

    // Resetea todos los campos del formulario
    public function limpiarCampos()
    {
        $this->reset([
            'cliente_id',
            'fechaFactura',
            'searchTermCliente',
            'searchTermCategoria',
            'searchTermProducto',
            'categoria_id',
            'producto_id',
            'codigoProducto',
            'metodo_pago',
            'montoPagado',
            'cambio',
            'subtotal',
            'ivaTotal',
            'ivaResumen',
            'total',
            'productosEnFactura',
            'tarjetaNombreTitular',
            'tarjetaNumero',
            'tarjetaFechaExpiracion',
            'tarjetaTipo',
        ]);
        $this->fechaFactura = Carbon::now()->format('Y-m-d');
        $this->proximoFacturaId = $this->generarNumeroFactura();
        $this->metodo_pago = 'Efectivo';
        $this->tarjetaTipo = 'Debito';
        $this->buscarClientes();
        $this->buscarCategorias();
        $this->resetValidation();
    }
}