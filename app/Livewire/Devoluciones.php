<?php

namespace App\Livewire;

use App\Models\Devolucion;
use App\Models\DetalleDevolucion;
use App\Models\Factura;
use App\Models\DetalleFactura;
use App\Models\Producto;
use App\Models\Empleado;
use App\Models\Temporalidad;
use App\Models\MovimientoInventario;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class Devoluciones extends Component
{
    public $proximoDevolucionId;
    public $fechaDevolucion;
    public $searchTermFacturaId = '';
    public $searchTermCliente = '';
    public $factura_id;
    public $motivoDevolucion = '';
    public $searchTermProducto = '';
    public $detalle_factura_id;
    public $cantidadDevuelta = 1;
    public $maxCantidadDevolver = 0;
    public $subtotal = 0;
    public $ivaTotal = 0;
    public $ivaResumen = 0;
    public $total = 0;
    public $showModalProducto = false;
    public $productosEnDevolucion = [];
    public $productosFiltrados = [];

    protected $messages = [
        'factura_id.required' => 'Debe seleccionar una factura.',
        'fechaDevolucion.required' => 'La fecha es obligatoria.',
        'motivoDevolucion.required' => 'El motivo de la devolución es obligatorio.',
        'motivoDevolucion.max' => 'El motivo no puede exceder los 500 caracteres.',
        'productosEnDevolucion.required' => 'Debe agregar al menos un producto a devolver.',
        'productosEnDevolucion.min' => 'Debe agregar al menos un producto a devolver.',
        'productosEnDevolucion.*.cantidad_devuelta.required' => 'La cantidad a devolver es obligatoria.',
        'productosEnDevolucion.*.cantidad_devuelta.min' => 'La cantidad a devolver debe ser al menos 1.',
        'productosEnDevolucion.*.cantidad_devuelta.max' => 'La cantidad a devolver no puede exceder la cantidad facturada.',
        'detalle_factura_id.required' => 'Debe seleccionar un producto.',
        'detalle_factura_id.exists' => 'El producto seleccionado no es válido.',
        'cantidadDevuelta.required' => 'La cantidad a devolver es obligatoria.',
        'cantidadDevuelta.min' => 'La cantidad a devolver debe ser al menos 1.',
        'cantidadDevuelta.max' => 'La cantidad a devolver no puede exceder la cantidad facturada.',
    ];
protected $listeners = [
    'check-unsaved-changes' => 'checkUnsavedChanges',
];

#[On('check-unsaved-changes')]
public function checkUnsavedChanges()
{
    Log::debug('check-unsaved-changes recibido en Devoluciones', [
        'factura_id' => $this->factura_id,
        'motivoDevolucion' => $this->motivoDevolucion,
        'productosEnDevolucion' => count($this->productosEnDevolucion)
    ]);
    $hasChanges = $this->hasUnsavedChanges();
    $this->dispatch('has-unsaved-changes', $hasChanges);
}

public function hasUnsavedChanges()
{
    $hasChanges = !empty($this->factura_id) || !empty($this->motivoDevolucion) || count($this->productosEnDevolucion) > 0;
    Log::debug('Verificando cambios no guardados en Devoluciones', [
        'hasChanges' => $hasChanges,
        'factura_id' => $this->factura_id,
        'motivoDevolucion' => $this->motivoDevolucion,
        'productosEnDevolucion' => count($this->productosEnDevolucion)
    ]);
    return $hasChanges;
}

    public function mount()
    {
        $this->fechaDevolucion = Carbon::now()->format('Y-m-d');
        $this->proximoDevolucionId = $this->generarNumeroDevolucion();
        $this->searchTermCliente = '';
        Log::debug('Componente Devoluciones montado', ['proximoDevolucionId' => $this->proximoDevolucionId]);
    }

    public function updatedSearchTermCliente($value)
    {
        Log::debug('searchTermCliente actualizado', [
            'value' => $value,
            'factura_id' => $this->factura_id,
            'searchTermFacturaId' => $this->searchTermFacturaId
        ]);
    }

    public function render()
    {
        return view('livewire.devoluciones', [
            'productosFiltrados' => $this->productosFiltrados,
        ]);
    }

    protected function rules()
    {
        $rules = [
            'factura_id' => 'required|exists:facturas,id',
            'fechaDevolucion' => 'required|date',
            'motivoDevolucion' => 'required|string|max:500',
            'productosEnDevolucion' => 'required|array|min:1',
            'productosEnDevolucion.*.detalle_factura_id' => 'required|exists:detalle_factura,id',
        ];

        foreach ($this->productosEnDevolucion as $index => $producto) {
            $max = $producto['cantidad_facturada'] ?? 1;
            $rules["productosEnDevolucion.{$index}.cantidad_devuelta"] = "required|numeric|min:1|max:{$max}";
        }

        return $rules;
    }

    protected function rulesForProducto()
    {
        return [
            'detalle_factura_id' => 'required|exists:detalle_factura,id',
            'cantidadDevuelta' => 'required|numeric|min:1|max:' . $this->maxCantidadDevolver,
        ];
    }

    private function generarNumeroDevolucion()
    {
        $ultimaDevolucion = Devolucion::latest('id')->first();
        $numero = $ultimaDevolucion ? intval($ultimaDevolucion->id) + 1 : 1;
        $numeroDevolucion = 'DEV' . str_pad($numero, 6, '0', STR_PAD_LEFT);
        Log::debug('Generando número de devolución', ['numeroDevolucion' => $numeroDevolucion]);
        return $numeroDevolucion;
    }

    public function updatedSearchTermFacturaId($value)
    {
        $this->searchTermFacturaId = preg_replace('/[^0-9]/', '', $value);
        $this->resetValidation('factura_id');
    }

    public function selectFacturaById()
    {
        if (trim($this->searchTermFacturaId) !== '') {
            $factura = Factura::with('cliente')->find($this->searchTermFacturaId);
            if ($factura) {
                $this->factura_id = $factura->id;
                $this->searchTermCliente = $factura->cliente ? ($factura->cliente->nombre . ' ' . $factura->cliente->apellido) : 'Sin cliente';
                $this->productosEnDevolucion = [];
                $this->calcularTotales();
                $this->buscarProductos();
                session()->flash('message', 'Factura seleccionada: ID ' . $factura->id);
                Log::debug('Factura seleccionada por ID', ['factura_id' => $this->factura_id, 'searchTermCliente' => $this->searchTermCliente]);
                $this->dispatch('refreshComponent');
            } else {
                $this->factura_id = null;
                $this->searchTermCliente = '';
                session()->flash('error', 'No se encontró una factura con ID ' . $this->searchTermFacturaId);
                $this->dispatch('refreshComponent');
            }
        } else {
            $this->factura_id = null;
            $this->searchTermCliente = '';
            $this->dispatch('refreshComponent');
        }
        $this->resetValidation('factura_id');
    }

    public function buscarProductos($term = null)
    {
        if (!$this->factura_id) {
            $this->productosFiltrados = [];
            Log::debug('No se buscaron productos, factura no seleccionada');
            return;
        }

        $term = $term ?? $this->searchTermProducto;
        $this->productosFiltrados = DetalleFactura::where('id_factura', $this->factura_id)
            ->whereHas('producto', function ($query) use ($term) {
                $query->where('nombre', 'like', "%{$term}%")
                    ->orWhere('marca', 'like', "%{$term}%")
                    ->orWhere('modelo', 'like', "%{$term}%")
                    ->orWhere('color', 'like', "%{$term}%");
            })
            ->with('producto')
            ->take(10)
            ->get();
        Log::debug('Productos filtrados', ['term' => $term, 'factura_id' => $this->factura_id, 'count' => $this->productosFiltrados->count()]);
    }

    public function updatedSearchTermProducto($value)
    {
        $this->buscarProductos($value);
        if ($value && $this->factura_id) {
            $detalle = DetalleFactura::whereHas('producto', function ($query) use ($value) {
                $query->where('nombre', $value);
            })->where('id_factura', $this->factura_id)->first();
            $this->detalle_factura_id = $detalle ? $detalle->id : null;
            $this->maxCantidadDevolver = $detalle ? $detalle->cantidad : 0;
        } else {
            $this->detalle_factura_id = null;
            $this->maxCantidadDevolver = 0;
        }
        $this->resetValidation('detalle_factura_id', 'cantidadDevuelta');
    }

    public function selectProducto($detalleFacturaId)
    {
        $detalle = DetalleFactura::with('producto')->find($detalleFacturaId);
        if ($detalle) {
            $this->detalle_factura_id = $detalle->id;
            $this->searchTermProducto = $detalle->producto->nombre;
            $this->maxCantidadDevolver = $detalle->cantidad;
            $this->cantidadDevuelta = 1;
            Log::debug('Producto seleccionado', ['detalle_factura_id' => $detalleFacturaId]);
        }
        $this->resetValidation('detalle_factura_id', 'cantidadDevuelta');
    }

    public function updatedProductosEnDevolucion()
    {
        $this->calcularTotales();
    }

    public function abrirModalProducto()
    {
        if (!$this->factura_id) {
            session()->flash('error', 'Seleccione una factura primero.');
            return;
        }
        $this->resetValidation();
        $this->searchTermProducto = '';
        $this->detalle_factura_id = null;
        $this->cantidadDevuelta = 1;
        $this->maxCantidadDevolver = 0;
        $this->buscarProductos();
        $this->showModalProducto = true;
    }

    public function cerrarModalProducto()
    {
        $this->showModalProducto = false;
    }

    public function agregarProducto()
    {
        $this->validate($this->rulesForProducto());

        $detalle = DetalleFactura::with('producto')->find($this->detalle_factura_id);
        if (!$detalle) {
            $this->addError('detalle_factura_id', 'Producto no encontrado.');
            return;
        }

        if ($this->cantidadDevuelta > $detalle->cantidad) {
            $this->addError('cantidadDevuelta', 'La cantidad a devolver no puede exceder la cantidad facturada.');
            return;
        }

        $existingIndex = collect($this->productosEnDevolucion)->search(function ($item) use ($detalle) {
            return $item['detalle_factura_id'] == $detalle->id;
        });

        if ($existingIndex !== false) {
            $this->productosEnDevolucion[$existingIndex]['cantidad_devuelta'] += $this->cantidadDevuelta;
            if ($this->productosEnDevolucion[$existingIndex]['cantidad_devuelta'] > $detalle->cantidad) {
                $this->productosEnDevolucion[$existingIndex]['cantidad_devuelta'] = $detalle->cantidad;
                session()->flash('warning', "No se puede devolver más de la cantidad facturada para '{$detalle->producto->nombre}'.");
            }
        } else {
            $subtotal = $this->cantidadDevuelta * $detalle->precio_unitario * (1 + $detalle->iva / 100);
            $this->productosEnDevolucion[] = [
                'detalle_factura_id' => $detalle->id,
                'producto_id' => $detalle->id_producto,
                'nombre_producto' => $detalle->producto->nombre,
                'marca_producto' => $detalle->producto->marca ?? '',
                'modelo_producto' => $detalle->producto->modelo ?? '',
                'color_producto' => $detalle->producto->color ?? '',
                'cantidad_facturada' => $detalle->cantidad,
                'cantidad_devuelta' => $this->cantidadDevuelta,
                'precio_unitario' => $detalle->precio_unitario,
                'iva' => $detalle->iva,
                'subtotal_devuelto' => $subtotal,
            ];
        }

        $this->calcularTotales();
        $this->cerrarModalProducto();
        session()->flash('message', "Producto '{$detalle->producto->nombre}' agregado para devolución.");
    }

    public function eliminarProducto($index)
    {
        unset($this->productosEnDevolucion[$index]);
        $this->productosEnDevolucion = array_values($this->productosEnDevolucion);
        $this->calcularTotales();
    }

    public function calcularTotales()
    {
        $this->subtotal = 0;
        $this->ivaTotal = 0;
        $this->total = 0;

        foreach ($this->productosEnDevolucion as $index => $item) {
            $subtotalItem = floatval($item['cantidad_devuelta'] ?? 0) * floatval($item['precio_unitario'] ?? 0);
            $ivaAmount = $subtotalItem * (floatval($item['iva'] ?? 0) / 100);
            $this->productosEnDevolucion[$index]['subtotal_devuelto'] = $subtotalItem + $ivaAmount;
            $this->subtotal += $subtotalItem;
            $this->ivaTotal += $ivaAmount;
        }

        $this->total = $this->subtotal + $this->ivaTotal;
        $this->ivaResumen = count($this->productosEnDevolucion) > 0 ? collect($this->productosEnDevolucion)->average('iva') : 0;
    }

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

    private function obtenerIdTemporalidadActual()
    {
        return $this->obtenerIdTemporalidad(Carbon::now()->toDateString());
    }

    private function generarPdfDevolucion($devolucion)
    {
        try {
            Log::info('Iniciando generación de PDF', ['devolucion_id' => $devolucion->id]);

            $factura = Factura::with('cliente')->find($devolucion->id_factura);
            if (!$factura) {
                Log::error('Factura no encontrada', ['id_factura' => $devolucion->id_factura]);
                throw new \Exception('Factura no encontrada.');
            }

            $pdfFileName = 'devolucion_' . $devolucion->id . '_' . Str::random(10) . '.pdf';
            $pdfPath = 'devoluciones/' . $pdfFileName;
            $fullPath = storage_path('app/public/' . $pdfPath);

            $pdf = Pdf::loadView('pdfs.devolucion', [
                'devolucion' => $devolucion,
                'factura' => $factura,
                'cliente' => $factura->cliente,
                'productos' => $this->productosEnDevolucion,
                'subtotal' => $this->subtotal,
                'ivaTotal' => $this->ivaTotal,
                'total' => $this->total,
                'motivo' => $this->motivoDevolucion,
            ])->setPaper('letter', 'portrait');

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

    public function procesarDevolucion()
    {
        try {
            Log::info('Iniciando procesarDevolucion', [
                'factura_id' => $this->factura_id,
                'fechaDevolucion' => $this->fechaDevolucion,
                'total' => $this->total,
                'producto_count' => count($this->productosEnDevolucion),
            ]);

            $this->validate();

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

            foreach ($this->productosEnDevolucion as $index => $item) {
                $detalle = DetalleFactura::find($item['detalle_factura_id']);
                if ($detalle && $item['cantidad_devuelta'] > $detalle->cantidad) {
                    $this->addError("productosEnDevolucion.{$index}.cantidad_devuelta", 'La cantidad a devolver excede la cantidad facturada para: ' . $item['nombre_producto']);
                    return;
                }
            }

            DB::beginTransaction();

            $idTemporalidadDevolucion = $this->obtenerIdTemporalidad($this->fechaDevolucion);

            $devolucion = Devolucion::create([
                'id_factura' => $this->factura_id,
                'id_empleado' => $idEmpleado,
                'fecha_devolucion' => $this->fechaDevolucion,
                'motivo_devolucion' => $this->motivoDevolucion,
                'monto_total_devuelto' => $this->total,
                'id_temporalidad' => $idTemporalidadDevolucion,
            ]);

            $this->proximoDevolucionId = 'DEV' . str_pad($devolucion->id, 6, '0', STR_PAD_LEFT);
            Log::info('Devolución creada', ['devolucion_id' => $devolucion->id]);

            foreach ($this->productosEnDevolucion as $item) {
                $detalle = DetalleFactura::find($item['detalle_factura_id']);
                $idTemporalidadDetalle = $this->obtenerIdTemporalidad($this->fechaDevolucion);

                DetalleDevolucion::create([
                    'id_devolucion' => $devolucion->id,
                    'id_detalle_factura' => $item['detalle_factura_id'],
                    'id_producto' => $item['producto_id'],
                    'cantidad_devuelta' => $item['cantidad_devuelta'],
                    'precio_unitario' => $item['precio_unitario'],
                    'iva' => $item['iva'],
                    'subtotal_devuelto' => $item['subtotal_devuelto'],
                    'id_temporalidad' => $idTemporalidadDetalle,
                ]);

                $producto = Producto::find($item['producto_id']);
                if ($producto) {
                    $producto->increment('stock', $item['cantidad_devuelta']);
                    $idTemporalidadMovimiento = $this->obtenerIdTemporalidadActual();
                    MovimientoInventario::create([
                        'id_producto' => $item['producto_id'],
                        'id_origen' => $devolucion->id,
                        'tipo' => 'ENTRADA',
                        'cantidad' => $item['cantidad_devuelta'],
                        'fecha' => $this->fechaDevolucion,
                        'descripcion' => "Entrada por devolución #{$this->proximoDevolucionId}",
                        'id_temporalidad' => $idTemporalidadMovimiento,
                    ]);
                }
            }

            $pdfPath = $this->generarPdfDevolucion($devolucion);
            DB::commit();
            session()->flash('message', 'Devolución registrada correctamente.');
            $this->limpiarCampos();

            return response()->download($pdfPath, 'comprobante_devolucion_DEV' . str_pad($devolucion->id, 6, '0', STR_PAD_LEFT) . '.pdf')->deleteFileAfterSend(true);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validación fallida: ' . json_encode($e->errors()));
            $this->setErrorBag($e->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar devolución: ' . $e->getMessage());
            session()->flash('error', 'Error al registrar la devolución: ' . $e->getMessage());
        }
    }

    public function limpiarCampos()
    {
        $this->reset([
            'factura_id',
            'fechaDevolucion',
            'searchTermFacturaId',
            'searchTermCliente',
            'motivoDevolucion',
            'searchTermProducto',
            'detalle_factura_id',
            'cantidadDevuelta',
            'maxCantidadDevolver',
            'subtotal',
            'ivaTotal',
            'ivaResumen',
            'total',
            'productosEnDevolucion',
            'productosFiltrados',
        ]);
        $this->fechaDevolucion = Carbon::now()->format('Y-m-d');
        $this->proximoDevolucionId = $this->generarNumeroDevolucion();
        $this->resetValidation();
    }
}