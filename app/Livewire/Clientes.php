<?php

namespace App\Livewire;

use App\Models\Cliente;
use App\Models\Temporalidad;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class Clientes extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Variables para la tabla
    public $search = '';

    // Variables para el modal
    public $showModal = false;
    public $isEditing = false;
    public $clienteId;

    // Variables para el modal de confirmación
    public $showConfirmModal = false;
    public $clienteIdToDeactivate;

    // Variables para mensajes
    public $showMessage = false;
    public $messageType = 'success';
    public $message = '';

    // Variables para el formulario
    public $nombre, $apellido, $telefono, $direccion;

    // Propiedades que se actualizan en tiempo real
    protected $queryString = ['search'];

    // Reglas de validación
    protected function rules()
    {
        return [
            'nombre' => 'required|string|max:255|not_regex:/^\s*$/',
            'apellido' => 'required|string|max:255|not_regex:/^\s*$/',
            'telefono' => 'required|string|max:15|not_regex:/^\s*$/',
            'direccion' => 'nullable|string',
        ];
    }

    // Mensajes de validación
    protected $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'nombre.not_regex' => 'El nombre no puede contener solo espacios.',
        'apellido.required' => 'El apellido es obligatorio.',
        'apellido.not_regex' => 'El apellido no puede contener solo espacios.',
        'telefono.required' => 'El teléfono es obligatorio.',
        'telefono.not_regex' => 'El teléfono no puede contener solo espacios.',
    ];

    public function mount()
    {
        $this->showMessage = false;
        Log::debug('Componente Clientes montado');
    }

    // Métodos para actualización en tiempo real
    public function updatedSearch()
    {
        $this->resetPage();
    }

    // Abrir modal para crear nuevo cliente
    public function openModal()
    {
        $this->resetValidation();
        $this->resetInputFields();
        $this->isEditing = false;
        $this->showModal = true;
        $this->showMessage = false;
        Log::debug('Abriendo modal para nuevo cliente');
    }

    // Cerrar modal
    public function closeModal()
    {
        $this->showModal = false;
        $this->resetValidation();
        $this->resetInputFields();
        $this->showMessage = false;
        Log::debug('Cerrando modal de cliente');
    }

    // Abrir modal de confirmación para desactivar
    public function confirmDelete($id)
    {
        $this->clienteIdToDeactivate = $id;
        $this->showConfirmModal = true;
        $this->showMessage = false;
        Log::debug("Abriendo modal de confirmación para desactivar cliente ID: {$id}");
    }

    // Cerrar modal de confirmación
    public function closeConfirmModal()
    {
        $this->showConfirmModal = false;
        $this->clienteIdToDeactivate = null;
        $this->showMessage = false;
        Log::debug('Cerrando modal de confirmación');
    }

    // Resetear formulario
    public function resetInputFields()
    {
        $this->reset([
            'clienteId',
            'nombre',
            'apellido',
            'telefono',
            'direccion',
        ]);
        $this->resetErrorBag();
    }

    // Cargar datos para editar
    public function edit($id)
    {
        try {
            $this->resetValidation();
            $this->isEditing = true;
            $this->clienteId = $id;

            $cliente = Cliente::withoutGlobalScope('active')->findOrFail($id);

            $this->nombre = $cliente->nombre;
            $this->apellido = $cliente->apellido;
            $this->telefono = $cliente->telefono;
            $this->direccion = $cliente->direccion === 'Opcional' ? '' : $cliente->direccion;

            $this->showModal = true;
            $this->showMessage = false;
            Log::debug("Cargado cliente para editar ID: {$id}");
        } catch (\Exception $e) {
            Log::error('Error al cargar cliente para editar ID ' . $id . ': ' . $e->getMessage());
            $this->message = 'Error al cargar el cliente para editar.';
            $this->messageType = 'error';
            $this->showMessage = true;
        }
    }

    /**
     * Busca o crea un registro de Temporalidad para una fecha dada.
     * Retorna el ID de la temporalidad o null si la fecha es inválida o hay error.
     */
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

    // Guardar nuevo cliente
    public function save()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $clienteData = [
                'nombre' => $this->nombre,
                'apellido' => $this->apellido,
                'telefono' => $this->telefono,
                'direccion' => empty(trim($this->direccion)) ? 'Opcional' : $this->direccion,
                'estado' => 'activo',
            ];

            if (Schema::hasColumn('clientes', 'id_temporalidad')) {
                $temporalidadId = $this->getTemporalidadIdForDate(now()->toDateString());
                $clienteData['id_temporalidad'] = $temporalidadId;
                if (!$temporalidadId) {
                    Log::warning("No se pudo obtener id_temporalidad para cliente con teléfono {$this->telefono}. Se guardará como NULL.");
                }
            }

            Cliente::create($clienteData);

            DB::commit();

            $this->message = 'Cliente creado correctamente.';
            $this->messageType = 'success';
            $this->showMessage = true;
            Log::debug("Mensaje establecido: Cliente creado correctamente para teléfono {$this->telefono}");
            $this->closeModal();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear cliente: ' . $e->getMessage());
            $this->message = 'Error al crear el cliente: ' . $e->getMessage();
            $this->messageType = 'error';
            $this->showMessage = true;
        }
    }

    // Actualizar cliente existente
    public function update()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $cliente = Cliente::withoutGlobalScope('active')->findOrFail($this->clienteId);

            $clienteData = [
                'nombre' => $this->nombre,
                'apellido' => $this->apellido,
                'telefono' => $this->telefono,
                'direccion' => empty(trim($this->direccion)) ? 'Opcional' : $this->direccion,
            ];

            if (Schema::hasColumn('clientes', 'id_temporalidad')) {
                $temporalidadId = $this->getTemporalidadIdForDate(now()->toDateString());
                $clienteData['id_temporalidad'] = $temporalidadId;
                if (!$temporalidadId) {
                    Log::warning("No se pudo obtener id_temporalidad al actualizar cliente ID {$this->clienteId}. Se guardará como NULL.");
                }
            }

            $cliente->update($clienteData);

            DB::commit();

            $this->message = 'Cliente actualizado correctamente.';
            $this->messageType = 'success';
            $this->showMessage = true;
            Log::debug("Mensaje establecido: Cliente actualizado correctamente para ID {$this->clienteId}");
            $this->closeModal();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar cliente ID ' . $this->clienteId . ': ' . $e->getMessage());
            $this->message = 'Error al actualizar el cliente: ' . $e->getMessage();
            $this->messageType = 'error';
            $this->showMessage = true;
        }
    }

    // Desactivar cliente
    public function deactivateCliente()
    {
        try {
            DB::beginTransaction();

            $cliente = Cliente::withoutGlobalScope('active')->findOrFail($this->clienteIdToDeactivate);

            $clienteData = [
                'estado' => 'inactivo',
            ];

            if (Schema::hasColumn('clientes', 'id_temporalidad')) {
                $temporalidadId = $this->getTemporalidadIdForDate(now()->toDateString());
                $clienteData['id_temporalidad'] = $temporalidadId;
                if (!$temporalidadId) {
                    Log::warning("No se pudo obtener id_temporalidad al desactivar cliente ID {$this->clienteIdToDeactivate}. Se guardará como NULL.");
                }
            }

            $cliente->update($clienteData);

            DB::commit();

            $this->message = 'Cliente marcado como inactivo correctamente.';
            $this->messageType = 'success';
            $this->showMessage = true;
            Log::debug("Mensaje establecido: Cliente desactivado correctamente para ID {$this->clienteIdToDeactivate}");
            $this->closeConfirmModal();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al desactivar cliente ID ' . $this->clienteIdToDeactivate . ': ' . $e->getMessage());
            $this->message = 'Error al marcar el cliente como inactivo: ' . $e->getMessage();
            $this->messageType = 'error';
            $this->showMessage = true;
            $this->closeConfirmModal();
        }
    }

    // Renderizar componente
    public function render()
    {
        try {
            $clientes = Cliente::query()
                ->when($this->search, function ($query) {
                    $query->where('nombre', 'like', '%' . $this->search . '%')
                        ->orWhere('apellido', 'like', '%' . $this->search . '%')
                        ->orWhere('telefono', 'like', '%' . $this->search . '%');
                })
                ->orderBy('id', 'desc')
                ->paginate(10);

            if ($this->showMessage) {
                Log::debug("Renderizando con mensaje: {$this->message} (tipo: {$this->messageType})");
            }

            return view('livewire.clientes', [
                'clientes' => $clientes,
            ]);
        } catch (\Exception $e) {
            Log::error('Error en render de clientes: ' . $e->getMessage());
            $this->message = 'Error crítico al cargar la lista de clientes.';
            $this->messageType = 'error';
            $this->showMessage = true;
            return view('livewire.clientes', [
                'clientes' => collect([]),
            ]);
        }
    }
}