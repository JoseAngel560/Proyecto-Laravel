<?php

namespace App\Livewire;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\MovimientoInventario;
use App\Models\Temporalidad;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class Inventario extends Component
{
    use WithPagination;
    
    public $activeTab = 'productos';
    
    public $buscarProducto = '';
    public $filtroCategoria = '';
    public $filtroStockMaximo = '';
    public $buscarCategoria = '';
    public $filtroTipoMovimiento = '';
    public $filtroFechaInicio = '';
    public $filtroFechaFin = '';
    public $buscarMovimiento = '';
    
    public $showProductoModal = false;
    public $showCategoriaModal = false;
    public $showMovimientoModal = false;
    public $showConfirmModal = false;
    public $isEditing = false;
    public $itemToDelete = null;
    public $deleteType = '';
    
    public $productoId;
    public $nombre;
    public $marca;
    public $modelo;
    public $color;
    public $categoria_id;
    public $precio_venta;
    public $stock;
    
    public $categoriaId;
    public $nombreCategoria;
    public $descripcionCategoria;
    
    public $tipo_movimiento;
    public $producto_id;
    public $cantidad;
    public $fecha_movimiento;
    public $descripcion_movimiento;
    public $searchTermCategoria = '';
    public $searchTermProducto = '';
    public $categoriasFiltradas = [];
    public $productosFiltrados = [];
    
    // Define las reglas de validación para los formularios
    protected function rules()
    {
        $rules = [
            'nombre' => 'required|string|max:255|not_regex:/^\s*$/',
            'marca' => 'required|string|max:100|not_regex:/^\s*$/',
            'modelo' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:50',
            'categoria_id' => 'required|exists:categorias,id',
            'precio_venta' => 'required|numeric|min:0',
            'nombreCategoria' => 'required|string|max:255',
            'descripcionCategoria' => 'nullable|string',
            'tipo_movimiento' => 'required|in:entrada,salida',
            'categoria_id' => 'required|exists:categorias,id', 
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
            'fecha_movimiento' => 'required|date',
            'descripcion_movimiento' => 'nullable|string',
        ];

        if (!$this->isEditing) {
            $rules['stock'] = 'required|integer|min:1';
        }

        return $rules;
    }
    
    // Define los mensajes personalizados para los errores de validación
    protected function messages()
    {
        return [
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'nombre.not_regex' => 'El nombre no puede contener solo espacios.',
            'marca.required' => 'La marca es obligatoria.',
            'marca.not_regex' => 'La marca no puede contener solo espacios.',
            'categoria_id.required' => 'Debe seleccionar una categoría.',
            'categoria_id.exists' => 'La categoría seleccionada no existe.',
            'precio_venta.required' => 'El precio de venta es obligatorio.',
            'precio_venta.numeric' => 'El precio de venta debe be un número.',
            'stock.required' => 'El stock inicial es obligatorio.',
            'stock.integer' => 'El stock debe ser un número entero.',
            'stock.min' => 'El stock inicial debe ser mayor a 0.',
            'nombreCategoria.required' => 'El nombre de la categoría es obligatorio.',
            'tipo_movimiento.required' => 'El tipo de movimiento es obligatorio.',
            'tipo_movimiento.in' => 'El tipo de movimiento debe ser entrada o salida.',
            'producto_id.required' => 'Debe seleccionar un producto.',
            'producto_id.exists' => 'El producto seleccionado no existe.',
            'cantidad.required' => 'La cantidad es obligatoria.',
            'cantidad.integer' => 'La cantidad debe ser un número entero.',
            'cantidad.min' => 'La cantidad debe ser al menos 1.',
            'fecha_movimiento.required' => 'La fecha es obligatoria.',
        ];
    }
    
    // Inicializa el componente y carga las categorías
    public function mount()
    {
        $this->buscarCategorias(); 
    }
    
    // Cambia la pestaña activa del componente
    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }
    
    // Abre el modal para crear un nuevo producto
    public function openProductoModal()
    {
        $this->resetValidation();
        $this->resetProductoForm();
        $this->isEditing = false;
        $this->showProductoModal = true;
    }
    
    // Cierra el modal de producto
    public function closeProductoModal()
    {
        $this->showProductoModal = false;
        $this->resetProductoForm();
    }
    
    // Carga los datos de un producto para edición
    public function editProducto($id)
    {
        $this->resetValidation();
        $this->isEditing = true;
        $this->productoId = $id;
        
        $producto = Producto::find($id);
        if ($producto) {
            $this->nombre = $producto->nombre;
            $this->marca = $producto->marca;
            $this->modelo = $producto->modelo;
            $this->color = $producto->color;
            $this->categoria_id = $producto->id_categoria;
            $this->precio_venta = $producto->precio_venta;
            $this->stock = $producto->stock; 
            
            $this->showProductoModal = true;
            Log::debug("Cargado producto para editar ID: {$id}");
        } else {
            Log::error("Producto no encontrado para editar ID: {$id}");
            session()->flash('error', 'El producto no existe.');
        }
    }
    
    // Guarda o actualiza un producto en la base de datos
    public function saveProducto()
    {
        $rules = [
            'nombre' => 'required|string|max:255|not_regex:/^\s*$/',
            'marca' => 'required|string|max:100|not_regex:/^\s*$/',
            'modelo' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:50',
            'categoria_id' => 'required|exists:categorias,id',
            'precio_venta' => 'required|numeric|min:0',
        ];

        if (!$this->isEditing) {
            $rules['stock'] = 'required|integer|min:1';
        }

        $this->validate($rules);

        try {
            DB::beginTransaction();

            $temporalidadId = $this->obtenerIdTemporalidadActual();

            if ($this->isEditing) {
                $producto = Producto::find($this->productoId);
                if ($producto) {
                    $producto->update([
                        'nombre' => $this->nombre,
                        'marca' => $this->marca,
                        'modelo' => $this->modelo,
                        'color' => $this->color,
                        'id_categoria' => $this->categoria_id,
                        'precio_venta' => $this->precio_venta,
                    ]);

                    session()->flash('message', 'Producto actualizado correctamente.');
                    Log::debug("Producto actualizado ID: {$this->productoId}");
                } else {
                    throw new \Exception('El producto no existe.');
                }
            } else {
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
                        'descripcion' => 'Stock inicial',
                        'id_temporalidad' => $temporalidadMovimientoId,
                    ]);
                }

                session()->flash('message', 'Producto creado correctamente.');
                Log::debug("Producto creado ID: {$producto->id}");
            }

            DB::commit();
            $this->closeProductoModal();
            $this->resetProductoForm();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar producto: ' . $e->getMessage());
            session()->flash('error', 'Error al guardar el producto: ' . $e->getMessage());
        }
    }
    
    // Abre el modal de confirmación para eliminar un producto o categoría
    public function confirmDelete($id, $type)
    {
        $this->itemToDelete = $id;
        $this->deleteType = $type;
        $this->showConfirmModal = true;
    }
    
    // Cierra el modal de confirmación
    public function closeConfirmModal()
    {
        $this->showConfirmModal = false;
        $this->itemToDelete = null;
        $this->deleteType = '';
    }
    
    // Ejecuta la eliminación confirmada de un producto o categoría
    public function deleteConfirmed()
    {
        if ($this->deleteType === 'producto') {
            $this->deactivateProducto($this->itemToDelete); 
        } elseif ($this->deleteType === 'categoria') {
            $this->deleteCategoria($this->itemToDelete);
        }
        
        $this->closeConfirmModal();
    }
    
    // Marca un producto como inactivo y registra el movimiento correspondiente
    public function deactivateProducto($id)
    {
        try {
            DB::beginTransaction();
            
            $producto = Producto::find($id);
            if (!$producto) {
                session()->flash('error', 'El producto no existe.');
                DB::commit();
                return;
            }

            if ($producto->stock > 0) {
                $temporalidadMovimientoId = $this->obtenerIdTemporalidadActual();
                
                MovimientoInventario::create([
                    'id_producto' => $producto->id,
                    'tipo' => 'salida',
                    'cantidad' => $producto->stock,
                    'fecha' => now(),
                    'descripcion' => "Producto ({$producto->nombre}, {$producto->marca}, {$producto->modelo}) marcado como inactivo",
                    'id_temporalidad' => $temporalidadMovimientoId,
                ]);
            }

            $producto->update([
                'estado' => 'inactivo',
            ]);

            session()->flash('message', 'Producto marcado como inactivo correctamente.');
            Log::debug("Producto marcado como inactivo ID: {$id}");
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al marcar producto como inactivo: ' . $e->getMessage());
            session()->flash('error', 'Error al marcar el producto como inactivo: ' . $e->getMessage());
        }
    }
    
    // Abre el modal para crear una nueva categoría
    public function openCategoriaModal()
    {
        $this->resetValidation();
        $this->resetCategoriaForm();
        $this->isEditing = false;
        $this->showCategoriaModal = true;
    }
    
    // Cierra el modal de categoría
    public function closeCategoriaModal()
    {
        $this->showCategoriaModal = false;
    }
    
    // Carga los datos de una categoría para edición
    public function editCategoria($id)
    {
        $this->resetValidation();
        $this->isEditing = true;
        $this->categoriaId = $id;
        
        $categoria = Categoria::find($id);
        if ($categoria) {
            $this->nombreCategoria = $categoria->nombre;
            $this->descripcionCategoria = $categoria->descripcion;
            
            $this->showCategoriaModal = true;
            Log::debug("Cargada categoría para editar ID: {$id}");
        } else {
            Log::error("Categoría no encontrada para editar ID: {$id}");
            session()->flash('error', 'La categoría no existe.');
        }
    }
    
    // Guarda o actualiza una categoría en la base de datos
    public function saveCategoria()
    {
        $this->validate([
            'nombreCategoria' => 'required|string|max:255',
            'descripcionCategoria' => 'nullable|string',
        ]);
        
        try {
            $temporalidadId = $this->obtenerIdTemporalidadActual();
            
            if ($this->isEditing) {
                $categoria = Categoria::find($this->categoriaId);
                if ($categoria) {
                    $categoria->update([
                        'nombre' => $this->nombreCategoria,
                        'descripcion' => $this->descripcionCategoria,
                    ]);
                    
                    session()->flash('message', 'Categoría actualizada correctamente.');
                    Log::debug("Categoría actualizada ID: {$this->categoriaId}");
                } else {
                    throw new \Exception('La categoría no existe.');
                }
            } else {
                $categoriaExistente = Categoria::where('nombre', $this->nombreCategoria)->first();
                if ($categoriaExistente) {
                    session()->flash('error', 'Ya existe una categoría con este nombre.');
                    return;
                }
                
                Categoria::create([
                    'nombre' => $this->nombreCategoria,
                    'descripcion' => $this->descripcionCategoria,
                    'id_temporalidad' => $temporalidadId,
                ]);
                
                session()->flash('message', 'Categoría creada correctamente.');
                Log::debug("Categoría creada: {$this->nombreCategoria}");
            }
            
            $this->closeCategoriaModal();
            $this->resetCategoriaForm();
            
        } catch (\Exception $e) {
            Log::error('Error al guardar categoría: ' . $e->getMessage());
            session()->flash('error', 'Error al guardar la categoría: ' . $e->getMessage());
        }
    }
    
    // Elimina una categoría si no tiene productos asociados
    public function deleteCategoria($id)
    {
        try {
            $categoria = Categoria::find($id);
            if ($categoria) {
                $tieneProductos = Producto::where('id_categoria', $id)
                    ->withoutGlobalScope('active')
                    ->exists();
                
                if ($tieneProductos) {
                    session()->flash('error', 'No se puede eliminar la categoría porque tiene productos asociados.');
                    return;
                }
                
                $categoria->delete();
                session()->flash('message', 'Categoría eliminada correctamente.');
                Log::debug("Categoría eliminada ID: {$id}");
            } else {
                session()->flash('error', 'La categoría no existe.');
            }
        } catch (\Exception $e) {
            Log::error('Error al eliminar categoría: ' . $e->getMessage());
            session()->flash('error', 'Error al eliminar la categoría: ' . $e->getMessage());
        }
    }
    
    // Abre el modal para registrar un nuevo movimiento de inventario
    public function openMovimientoModal()
    {
        $this->resetValidation();
        $this->resetMovimientoForm();
        $this->buscarCategorias();
        $this->showMovimientoModal = true;
    }
    
    // Cierra el modal de movimiento
    public function closeMovimientoModal()
    {
        $this->showMovimientoModal = false;
        $this->resetMovimientoForm();
    }
    
    // Registra un movimiento de inventario y actualiza el stock
    public function saveMovimiento()
    {
        $this->validate([
            'tipo_movimiento' => 'required|in:entrada,salida',
            'categoria_id' => 'required|exists:categorias,id',
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
            'fecha_movimiento' => 'required|date',
            'descripcion_movimiento' => 'nullable|string',
        ]);
        
        try {
            DB::beginTransaction();
            
            $temporalidadId = $this->obtenerIdTemporalidad($this->fecha_movimiento);
            
            $producto = Producto::find($this->producto_id);
            
            if ($producto->id_categoria != $this->categoria_id) {
                session()->flash('error', 'El producto seleccionado no pertenece a la categoría seleccionada.');
                return;
            }
            if ($this->tipo_movimiento === 'salida' && $producto->stock < $this->cantidad) {
                session()->flash('error', 'No hay suficiente stock para realizar esta salida.');
                return;
            }
            
            MovimientoInventario::create([
                'id_producto' => $this->producto_id,
                'tipo' => $this->tipo_movimiento,
                'cantidad' => $this->cantidad,
                'fecha' => $this->fecha_movimiento,
                'descripcion' => $this->descripcion_movimiento,
                'id_temporalidad' => $temporalidadId,
            ]);
            
            if ($this->tipo_movimiento === 'entrada') {
                $producto->increment('stock', $this->cantidad);
            } else {
                $producto->decrement('stock', $this->cantidad);
            }
            
            DB::commit();
            
            session()->flash('message', 'Movimiento registrado correctamente.');
            Log::debug("Movimiento registrado para producto ID: {$this->producto_id}");
            $this->closeMovimientoModal();
            $this->resetMovimientoForm();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar movimiento: ' . $e->getMessage());
            session()->flash('error', 'Error al registrar el movimiento: ' . $e->getMessage());
        }
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
            $this->productosFiltradas = [];
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
            ->where('estado', 'activo') 
            ->orderBy('nombre')
            ->take(10)
            ->get();
        Log::debug('Productos filtrados', ['term' => $term, 'categoria_id' => $this->categoria_id, 'count' => $this->productosFiltrados->count()]);
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
                ->where('estado', 'activo')
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
    
    // Resetea los campos del formulario de producto
    private function resetProductoForm()
    {
        $this->productoId = null;
        $this->nombre = '';
        $this->marca = '';
        $this->modelo = '';
        $this->color = '';
        $this->categoria_id = '';
        $this->precio_compra = '';
        $this->precio_venta = '';
        $this->stock = 0;
    }
    
    // Resetea los campos del formulario de categoría
    private function resetCategoriaForm()
    {
        $this->categoriaId = null;
        $this->nombreCategoria = '';
        $this->descripcionCategoria = '';
    }
    
    // Resetea los campos del formulario de movimiento
    private function resetMovimientoForm()
    {
        $this->tipo_movimiento = '';
        $this->categoria_id = null;
        $this->producto_id = null;
        $this->cantidad = 1;
        $this->fecha_movimiento = now()->format('Y-m-d\TH:i');
        $this->descripcion_movimiento = '';
        $this->searchTermCategoria = '';
        $this->searchTermProducto = '';
        $this->categoriasFiltradas = [];
        $this->productosFiltrados = [];
    }
    
    // Obtiene la lista de productos con filtros aplicados
    public function getProductosProperty()
    {
        $query = Producto::with('categoria');
        
        if (!empty($this->buscarProducto)) {
            $query->where(function($q) {
                $q->where('nombre', 'like', '%' . $this->buscarProducto . '%')
                  ->orWhere('marca', 'like', '%' . $this->buscarProducto . '%')
                  ->orWhere('modelo', 'like', '%' . $this->buscarProducto . '%');
            });
        }
        
        if (!empty($this->filtroCategoria)) {
            $query->where('id_categoria', $this->filtroCategoria);
        }
        
        if (!empty($this->filtroStockMaximo) && is_numeric($this->filtroStockMaximo)) {
            $query->where('stock', '<=', $this->filtroStockMaximo);
        }
        
        return $query->orderBy('nombre')->get();
    }
    
    // Obtiene la lista de categorías con filtros aplicados
    public function getCategoriasProperty()
    {
        $query = Categoria::query();
        
        if (!empty($this->buscarCategoria)) {
            $query->where('nombre', 'like', '%' . $this->buscarCategoria . '%');
        }
        
        return $query->orderBy('nombre')->get();
    }
    
    // Obtiene la lista de movimientos de inventario con filtros aplicados
    public function getMovimientosProperty()
    {
        $query = MovimientoInventario::with(['producto' => function ($query) {
            $query->withoutGlobalScope('active'); 
        }]);
        
        if (!empty($this->filtroTipoMovimiento)) {
            $query->where('tipo', $this->filtroTipoMovimiento);
        }
        
        if (!empty($this->filtroFechaInicio)) {
            $query->whereDate('fecha', '>=', $this->filtroFechaInicio);
        }
        
        if (!empty($this->filtroFechaFin)) {
            $query->whereDate('fecha', '<=', $this->filtroFechaFin);
        }
        
        if (!empty($this->buscarMovimiento)) {
            $searchTerm = '%' . $this->buscarMovimiento . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->whereHas('producto', function($q) use ($searchTerm) {
                    $q->withoutGlobalScope('active')
                      ->where('nombre', 'like', $searchTerm)
                      ->orWhere('marca', 'like', $searchTerm)
                      ->orWhere('modelo', 'like', $searchTerm);
                })
                ->orWhere('descripcion', 'like', $searchTerm);
            });
        }
        
        return $query->orderBy('fecha', 'desc')->get();
    }
    
    // Renderiza la vista del componente con los datos filtrados
    public function render()
    {
        if (session()->has('message')) {
            Log::debug('Sesión contiene mensaje de éxito: ' . session('message'));
        }
        if (session()->has('error')) {
            Log::debug('Sesión contiene mensaje de error: ' . session('error'));
        }

        return view('livewire.inventario', [
            'productos' => $this->productos,
            'categorias' => $this->categorias,
            'movimientos' => $this->movimientos,
            'productosActivos' => Producto::orderBy('nombre')->get(), 
        ]);
    }
}