<?php

namespace App\Livewire;

use App\Models\Proveedor;
use App\Models\Temporalidad;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class Proveedores extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';

    public $showModal = false;
    public $isEditing = false;
    public $proveedorId;

    public $showConfirmModal = false;
    public $proveedorIdToDeactivate;

    public $showMessage = false;
    public $messageType = 'success';
    public $message = '';

    public $nombre, $razon_social, $contacto, $telefono, $email, $direccion, $ruc, $fecha_registro;

    protected $queryString = ['search'];

    // Define las reglas de validación para el formulario de proveedores
    protected function rules()
    {
        $rucRule = 'required|string|max:20|unique:proveedores,ruc|not_regex:/^\s*$/';
        if ($this->isEditing) {
            $rucRule = 'required|string|max:20|unique:proveedores,ruc,' . $this->proveedorId . '|not_regex:/^\s*$/';
        }
        return [
            'nombre' => 'required|string|max:255|not_regex:/^\s*$/',
            'razon_social' => 'required|string|max:255|not_regex:/^\s*$/',
            'contacto' => 'required|string|max:255|not_regex:/^\s*$/',
            'telefono' => 'required|string|max:20|not_regex:/^\s*$/',
            'email' => 'required|email|max:255|not_regex:/^\s*$/',
            'direccion' => 'required|string|max:500|not_regex:/^\s*$/',
            'ruc' => $rucRule,
            'fecha_registro' => 'required|date',
        ];
    }

    // Define los mensajes personalizados para los errores de validación
    protected $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'nombre.not_regex' => 'El nombre no puede contener solo espacios.',
        'razon_social.required' => 'La razón social es obligatoria.',
        'razon_social.not_regex' => 'La razón social no puede contener solo espacios.',
        'contacto.required' => 'El contacto es obligatorio.',
        'contacto.not_regex' => 'El contacto no puede contener solo espacios.',
        'telefono.required' => 'El teléfono es obligatorio.',
        'telefono.not_regex' => 'El teléfono no puede contener solo espacios.',
        'email.required' => 'El email es obligatorio.',
        'email.email' => 'El email debe ser válido.',
        'email.not_regex' => 'El email no puede contener solo espacios.',
        'direccion.required' => 'La dirección es obligatoria.',
        'direccion.not_regex' => 'La dirección no puede contener solo espacios.',
        'ruc.required' => 'El RUC es obligatorio.',
        'ruc.unique' => 'Este RUC ya está registrado.',
        'ruc.not_regex' => 'El RUC no puede contener solo espacios.',
        'fecha_registro.required' => 'La fecha de registro es obligatoria.',
        'fecha_registro.date' => 'Formato de fecha inválido.',
    ];

    // Inicializa el componente con valores por defecto
    public function mount()
    {
        $this->fecha_registro = date('Y-m-d');
        $this->showMessage = false;
        Log::debug('Componente Proveedores montado');
    }

    // Resetea la paginación al actualizar el término de búsqueda
    public function updatedSearch()
    {
        $this->resetPage();
    }

    // Abre el modal para crear un nuevo proveedor
    public function openModal()
    {
        $this->resetValidation();
        $this->resetInputFields();
        $this->isEditing = false;
        $this->showModal = true;
        $this->showMessage = false;
        Log::debug('Abriendo modal para nuevo proveedor');
    }

    // Cierra el modal de creación/edición
    public function closeModal()
    {
        $this->showModal = false;
        $this->resetValidation();
        $this->resetInputFields();
        $this->showMessage = false;
        Log::debug('Cerrando modal de proveedor');
    }

    // Abre el modal de confirmación para desactivar un proveedor
    public function confirmDelete($id)
    {
        $this->proveedorIdToDeactivate = $id;
        $this->showConfirmModal = true;
        $this->showMessage = false;
        Log::debug("Abriendo modal de confirmación para desactivar proveedor ID: {$id}");
    }

    // Cierra el modal de confirmación
    public function closeConfirmModal()
    {
        $this->showConfirmModal = false;
        $this->proveedorIdToDeactivate = null;
        $this->showMessage = false;
        Log::debug('Cerrando modal de confirmación');
    }

    // Resetea los campos del formulario
    public function resetInputFields()
    {
        $this->reset([
            'proveedorId',
            'nombre',
            'razon_social',
            'contacto',
            'telefono',
            'email',
            'direccion',
            'ruc'
        ]);
        $this->fecha_registro = date('Y-m-d');
        $this->resetErrorBag();
    }

    // Carga los datos de un proveedor para edición
    public function edit($id)
    {
        try {
            $this->resetValidation();
            $this->isEditing = true;
            $this->proveedorId = $id;

            $proveedor = Proveedor::withoutGlobalScope('active')->findOrFail($id);

            $this->nombre = $proveedor->nombre;
            $this->razon_social = $proveedor->razon_social;
            $this->contacto = $proveedor->contacto;
            $this->telefono = $proveedor->telefono;
            $this->email = $proveedor->email;
            $this->direccion = $proveedor->direccion;
            $this->ruc = $proveedor->ruc;
            $this->fecha_registro = optional($proveedor->fecha_registro)->format('Y-m-d') ?? date('Y-m-d');

            $this->showModal = true;
            $this->showMessage = false;
            Log::debug("Cargado proveedor para editar ID: {$id}");
        } catch (\Exception $e) {
            Log::error('Error al cargar proveedor para editar ID ' . $id . ': ' . $e->getMessage());
            $this->message = 'Error al cargar el proveedor para editar.';
            $this->messageType = 'error';
            $this->showMessage = true;
        }
    }

    // Obtiene o crea un ID de temporalidad para una fecha específica
    private function getTemporalidadIdForDate(?string $dateString): ?int
    {
        if (!$dateString) {
            Log::warning("Se intentó obtener Temporalidad ID para una fecha nula.");
            return null;
        }

        try {
            $fecha = Carbon::parse($dateString)->startOfDay();
            $temporalidad = Temporalidad::firstOrCreate(
                ['fecha_completa' => $fecha->toDateString()],
                [
                    'dia_semana' => $fecha->isoFormat('dddd'),
                    'dia_mes' => $fecha->day,
                    'semana_mes' => $fecha->weekOfMonth,
                    'dia_anio' => $fecha->dayOfYear,
                    'semana_anio' => $fecha->weekOfYear,
                    'trimestre_anio' => $fecha->quarter,
                    'mes_anio' => $fecha->isoFormat('MMMM'),
                    'anio' => $fecha->year,
                    'vispera_festivo' => false
                ]
            );
            return $temporalidad->id;
        } catch (\Exception $e) {
            Log::error("Error al buscar/crear Temporalidad para fecha '{$dateString}': " . $e->getMessage());
            return null;
        }
    }

    // Crea un nuevo proveedor en la base de datos
    public function save()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $proveedorData = [
                'nombre' => $this->nombre,
                'razon_social' => $this->razon_social,
                'contacto' => $this->contacto,
                'telefono' => $this->telefono,
                'email' => $this->email,
                'direccion' => $this->direccion,
                'ruc' => $this->ruc,
                'fecha_registro' => $this->fecha_registro,
                'estado' => 'activo',
            ];

            if (Schema::hasColumn('proveedores', 'id_temporalidad')) {
                $temporalidadId = $this->getTemporalidadIdForDate($this->fecha_registro);
                $proveedorData['id_temporalidad'] = $temporalidadId;
                if (!$temporalidadId) {
                    Log::warning("No se pudo obtener id_temporalidad para proveedor con RUC {$this->ruc}. Se guardará como NULL.");
                }
            }

            Proveedor::create($proveedorData);

            DB::commit();

            $this->message = 'Proveedor creado correctamente.';
            $this->messageType = 'success';
            $this->showMessage = true;
            Log::debug("Mensaje establecido: Proveedor creado correctamente para RUC {$this->ruc}");
            $this->closeModal();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear proveedor: ' . $e->getMessage());
            $this->message = 'Error al crear el proveedor: ' . $e->getMessage();
            $this->messageType = 'error';
            $this->showMessage = true;
        }
    }

    // Actualiza un proveedor existente en la base de datos
    public function update()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $proveedor = Proveedor::withoutGlobalScope('active')->findOrFail($this->proveedorId);

            $proveedorData = [
                'nombre' => $this->nombre,
                'razon_social' => $this->razon_social,
                'contacto' => $this->contacto,
                'telefono' => $this->telefono,
                'email' => $this->email,
                'direccion' => $this->direccion,
                'ruc' => $this->ruc,
                'fecha_registro' => $this->fecha_registro,
            ];

            if (Schema::hasColumn('proveedores', 'id_temporalidad')) {
                $temporalidadId = $this->getTemporalidadIdForDate($this->fecha_registro);
                $proveedorData['id_temporalidad'] = $temporalidadId;
                if (!$temporalidadId) {
                    Log::warning("No se pudo obtener id_temporalidad al actualizar proveedor ID {$this->proveedorId}. Se guardará como NULL.");
                }
            }

            $proveedor->update($proveedorData);

            DB::commit();

            $this->message = 'Proveedor actualizado correctamente.';
            $this->messageType = 'success';
            $this->showMessage = true;
            Log::debug("Mensaje establecido: Proveedor actualizado correctamente para ID {$this->proveedorId}");
            $this->closeModal();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar proveedor ID ' . $this->proveedorId . ': ' . $e->getMessage());
            $this->message = 'Error al actualizar el proveedor: ' . $e->getMessage();
            $this->messageType = 'error';
            $this->showMessage = true;
        }
    }

    // Marca un proveedor como inactivo
    public function deactivateProveedor()
    {
        try {
            DB::beginTransaction();

            $proveedor = Proveedor::withoutGlobalScope('active')->findOrFail($this->proveedorIdToDeactivate);

            $proveedorData = [
                'estado' => 'inactivo',
            ];

            if (Schema::hasColumn('proveedores', 'id_temporalidad')) {
                $temporalidadId = $this->getTemporalidadIdForDate(date('Y-m-d'));
                $proveedorData['id_temporalidad'] = $temporalidadId;
                if (!$temporalidadId) {
                    Log::warning("No se pudo obtener id_temporalidad al desactivar proveedor ID {$this->proveedorIdToDeactivate}. Se guardará como NULL.");
                }
            }

            $proveedor->update($proveedorData);

            DB::commit();

            $this->message = 'Proveedor marcado como inactivo correctamente.';
            $this->messageType = 'success';
            $this->showMessage = true;
            Log::debug("Mensaje establecido: Proveedor desactivado correctamente para ID {$this->proveedorIdToDeactivate}");
            $this->closeConfirmModal();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al desactivar proveedor ID ' . $this->proveedorIdToDeactivate . ': ' . $e->getMessage());
            $this->message = 'Error al marcar el proveedor como inactivo: ' . $e->getMessage();
            $this->messageType = 'error';
            $this->showMessage = true;
            $this->closeConfirmModal();
        }
    }

    // Renderiza la vista con la lista de proveedores filtrada
    public function render()
    {
        try {
            $proveedores = Proveedor::query()
                ->when($this->search, function ($query) {
                    $query->where('nombre', 'like', '%' . $this->search . '%')
                        ->orWhere('razon_social', 'like', '%' . $this->search . '%')
                        ->orWhere('contacto', 'like', '%' . $this->search . '%')
                        ->orWhere('telefono', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                        ->orWhere('ruc', 'like', '%' . $this->search . '%');
                })
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            if ($this->showMessage) {
                Log::debug("Renderizando con mensaje: {$this->message} (tipo: {$this->messageType})");
            }

            return view('livewire.proveedores', [
                'proveedores' => $proveedores,
            ]);
        } catch (\Exception $e) {
            Log::error('Error en render de proveedores: ' . $e->getMessage());
            $this->message = 'Error crítico al cargar la lista de proveedores.';
            $this->messageType = 'error';
            $this->showMessage = true;
            return view('livewire.proveedores', [
                'proveedores' => collect([]),
            ]);
        }
    }
}