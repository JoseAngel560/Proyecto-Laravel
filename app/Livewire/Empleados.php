<?php

namespace App\Livewire;

use App\Models\Empleado;
use App\Models\Temporalidad;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class Empleados extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $filterCargo = '';

    public $showModal = false;
    public $isEditing = false;
    public $empleadoId;

    public $showConfirmModal = false;
    public $empleadoIdToDelete;

    public $showMessage = false;
    public $message = '';
    public $messageType = 'success'; 

    public $nombre;
    public $apellido;
    public $cedula;
    public $telefono;
    public $email;
    public $direccion;
    public $cargo;
    public $salario;
    public $fecha_contratacion;
    public $usuario;
    public $contraseña;

    protected $queryString = ['search', 'filterCargo'];

    protected function rules()
    {
        $cedulaRule = 'required|string|max:20|unique:empleados,cedula|not_regex:/^\s*$/';
        $usuarioRule = 'nullable|string|max:50|unique:empleados,usuario|not_regex:/^\s*$/';
        if ($this->isEditing) {
            $cedulaRule = 'required|string|max:20|unique:empleados,cedula,' . $this->empleadoId . '|not_regex:/^\s*$/';
            $usuarioRule = 'nullable|string|max:50|unique:empleados,usuario,' . $this->empleadoId . '|not_regex:/^\s*$/';
        }
        return [
            'nombre' => 'required|string|max:255|not_regex:/^\s*$/',
            'apellido' => 'required|string|max:255|not_regex:/^\s*$/',
            'cedula' => $cedulaRule,
            'telefono' => 'required|string|max:15|not_regex:/^\s*$/',
            'email' => 'nullable|email|max:255',
            'direccion' => 'nullable|string',
            'cargo' => 'required|in:Vendedor,Administrador,Inventario,Técnico',
            'salario' => 'required|numeric|min:0.01',
            'fecha_contratacion' => 'required|date',
            'usuario' => $usuarioRule,
            'contraseña' => $this->isEditing ? 'nullable|string|min:6|max:255' : 'required|string|min:6|max:255|not_regex:/^\s*$/',
        ];
    }

    protected $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'nombre.not_regex' => 'El nombre no puede contener solo espacios.',
        'apellido.required' => 'El apellido es obligatorio.',
        'apellido.not_regex' => 'El apellido no puede contener solo espacios.',
        'cedula.required' => 'La cédula es obligatoria.',
        'cedula.unique' => 'Esta cédula ya está registrada.',
        'cedula.not_regex' => 'La cédula no puede contener solo espacios.',
        'telefono.required' => 'El teléfono es obligatorio.',
        'telefono.not_regex' => 'El teléfono no puede contener solo espacios.',
        'cargo.required' => 'El cargo es obligatorio.',
        'cargo.in' => 'El cargo seleccionado no es válido.',
        'salario.required' => 'El salario es obligatorio.',
        'salario.numeric' => 'El salario debe ser un número.',
        'salario.min' => 'El salario debe ser mayor a 0.',
        'fecha_contratacion.required' => 'La fecha de contratación es obligatoria.',
        'fecha_contratacion.date' => 'Formato de fecha inválido.',
        'usuario.unique' => 'Este usuario ya está registrado.',
        'usuario.not_regex' => 'El usuario no puede contener solo espacios.',
        'contraseña.required' => 'La contraseña es obligatoria para nuevos empleados.',
        'contraseña.min' => 'La contraseña debe tener al menos 6 caracteres.',
        'contraseña.not_regex' => 'La contraseña no puede contener solo espacios.',
    ];

    protected function getListeners()
    {
        return [
            'search' => 'performSearch',
            'filterByCargo' => 'performFilterByCargo'
        ];
    }

    public function mount()
    {
        $this->fecha_contratacion = date('Y-m-d');
        $this->showMessage = false;
        Log::debug('Componente Empleados montado');
    }

    public function performSearch($value)
    {
        $this->search = $value;
        $this->resetPage();
    }

    public function performFilterByCargo($value)
    {
        $this->filterCargo = $value;
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterCargo()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        $this->resetValidation();
        $this->resetForm();
        $this->showModal = true;
        $this->isEditing = false;
        $this->showMessage = false;
        Log::debug('Abriendo modal para nuevo empleado');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetValidation();
        $this->resetForm();
        $this->showMessage = false;
        Log::debug('Cerrando modal de empleado');
    }

    public function confirmDelete($id)
    {
        $this->empleadoIdToDelete = $id;
        $this->showConfirmModal = true;
        $this->showMessage = false;
        Log::debug("Abriendo modal de confirmación para desactivar empleado ID: {$id}");
    }

    public function closeConfirmModal()
    {
        $this->showConfirmModal = false;
        $this->empleadoIdToDelete = null;
        $this->showMessage = false;
        $this->message = '';
        $this->messageType = 'success';
        Log::debug('Cerrando modal de confirmación');
    }

    public function resetForm()
    {
        $this->reset([
            'empleadoId',
            'nombre',
            'apellido',
            'cedula',
            'telefono',
            'email',
            'direccion',
            'cargo',
            'salario',
            'usuario',
            'contraseña'
        ]);
        $this->fecha_contratacion = date('Y-m-d');
    }

    public function edit($id)
    {
        try {
            $this->resetValidation();
            $this->isEditing = true;
            $this->empleadoId = $id;

            $empleado = Empleado::findOrFail($id);

            $this->nombre = $empleado->nombre;
            $this->apellido = $empleado->apellido;
            $this->cedula = $empleado->cedula;
            $this->telefono = $empleado->telefono;
            $this->email = $empleado->email;
            $this->direccion = $empleado->direccion;
            $this->cargo = $empleado->cargo;
            $this->salario = $empleado->salario;
            $this->fecha_contratacion = optional($empleado->fecha_contratacion)->format('Y-m-d') ?? date('Y-m-d');
            $this->usuario = $empleado->usuario;
            $this->contraseña = null;

            $this->showModal = true;
            $this->showMessage = false;
            Log::debug("Cargado empleado para editar ID: {$id}");
        } catch (\Exception $e) {
            Log::error('Error al cargar empleado para editar ID ' . $id . ': ' . $e->getMessage());
            $this->message = 'Error al cargar el empleado para editar.';
            $this->messageType = 'error';
            $this->showMessage = true;
        }
    }


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

    public function save()
    {
        $this->validate();

        try {
            $empleadoData = [
                'nombre' => $this->nombre,
                'apellido' => $this->apellido,
                'cedula' => $this->cedula,
                'telefono' => $this->telefono,
                'email' => $this->email,
                'direccion' => $this->direccion,
                'cargo' => $this->cargo,
                'salario' => $this->salario,
                'fecha_contratacion' => $this->fecha_contratacion,
                'usuario' => $this->usuario,
                'contraseña' => $this->contraseña ? Hash::make($this->contraseña) : null,
                'estado' => 'activo', 
            ];

            if (Schema::hasColumn('empleados', 'id_temporalidad')) {
                $temporalidadId = $this->getTemporalidadIdForDate($this->fecha_contratacion);
                $empleadoData['id_temporalidad'] = $temporalidadId;
                if (!$temporalidadId) {
                    Log::warning("No se pudo obtener id_temporalidad para empleado con cédula {$this->cedula}. Se guardará como NULL.");
                }
            }

            Empleado::create($empleadoData);

            $this->message = 'Empleado creado correctamente.';
            $this->messageType = 'success';
            $this->showMessage = true;
            Log::debug("Mensaje establecido: Empleado creado correctamente para cédula {$this->cedula}");
            $this->closeModal();

        } catch (\Exception $e) {
            Log::error('Error al crear empleado: ' . $e->getMessage());
            $this->message = 'Error al crear el empleado: ' . $e->getMessage();
            $this->messageType = 'error';
            $this->showMessage = true;
        }
    }

    public function update()
    {
        $this->validate();

        try {
            $empleado = Empleado::findOrFail($this->empleadoId);

            $empleadoData = [
                'nombre' => $this->nombre,
                'apellido' => $this->apellido,
                'cedula' => $this->cedula,
                'telefono' => $this->telefono,
                'email' => $this->email,
                'direccion' => $this->direccion,
                'cargo' => $this->cargo,
                'salario' => $this->salario,
                'fecha_contratacion' => $this->fecha_contratacion,
                'usuario' => $this->usuario,
            ];

            if (Schema::hasColumn('empleados', 'id_temporalidad')) {
                $temporalidadId = $this->getTemporalidadIdForDate($this->fecha_contratacion);
                $empleadoData['id_temporalidad'] = $temporalidadId;
                if (!$temporalidadId) {
                    Log::warning("No se pudo obtener id_temporalidad al actualizar empleado ID {$this->empleadoId}. Se guardará como NULL.");
                }
            }

            $empleado->update($empleadoData);

            if ($this->contraseña) {
                $empleado->update([
                    'contraseña' => Hash::make($this->contraseña)
                ]);
            }

            $this->message = 'Empleado actualizado correctamente.';
            $this->messageType = 'success';
            $this->showMessage = true;
            Log::debug("Mensaje establecido: Empleado actualizado correctamente para ID {$this->empleadoId}");
            $this->closeModal();

        } catch (\Exception $e) {
            Log::error('Error al actualizar empleado ID ' . $this->empleadoId . ': ' . $e->getMessage());
            $this->message = 'Error al actualizar el empleado: ' . $e->getMessage();
            $this->messageType = 'error';
            $this->showMessage = true;
        }
    }

    public function deleteConfirmed()
    {
        try {
            $empleado = Empleado::findOrFail($this->empleadoIdToDelete);

            $idEmpleado = session('empleado_id');
            if (!$idEmpleado) {
                $usuario = session('usuario');
                if ($usuario) {
                    $empleadoAutenticado = Empleado::where('usuario', $usuario)->first();
                    $idEmpleado = $empleadoAutenticado ? $empleadoAutenticado->id : null;
                }
            }

            if ($idEmpleado && $idEmpleado == $this->empleadoIdToDelete) {
                $this->message = 'No puedes desactivar tu propio usuario mientras estás usando el sistema.';
                $this->messageType = 'error';
                $this->showMessage = true;
                Log::warning("Intento de autodesactivación bloqueado para empleado ID {$this->empleadoIdToDelete} (usuario: {$empleado->usuario})");
                return;
            }

            $empleado->update(['estado' => 'inactivo']);

            $this->message = 'Empleado marcado como inactivo correctamente.';
            $this->messageType = 'success';
            $this->showMessage = true;
            Log::debug("Mensaje establecido: Empleado marcado como inactivo para ID {$this->empleadoIdToDelete}");
            $this->closeConfirmModal();

        } catch (\Exception $e) {
            Log::error('Error al desactivar empleado ID ' . $this->empleadoIdToDelete . ': ' . $e->getMessage());
            $this->message = 'Error al desactivar el empleado: ' . $e->getMessage();
            $this->messageType = 'error';
            $this->showMessage = true;
        }
    }

    public function render()
    {
        try {
            $query = Empleado::query();

            $query->where('estado', 'activo');

            if ($this->search) {
                $query->where(function($q) {
                    $q->where('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('apellido', 'like', '%' . $this->search . '%')
                      ->orWhere('cedula', 'like', '%' . $this->search . '%')
                      ->orWhere('telefono', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            }

            if ($this->filterCargo) {
                $query->where('cargo', $this->filterCargo);
            }

            $cargos = Empleado::select('cargo')
                ->where('estado', 'activo')
                ->distinct()
                ->whereNotNull('cargo')
                ->orderBy('cargo')
                ->pluck('cargo');

            $empleados = $query->orderBy('id', 'desc')->paginate(10);

            if ($this->showMessage) {
                Log::debug("Renderizando con mensaje: {$this->message} (tipo: {$this->messageType})");
            }

            return view('livewire.empleados', [
                'empleados' => $empleados,
                'cargos' => $cargos
            ]);
        } catch (\Exception $e) {
            Log::error('Error en render de empleados: ' . $e->getMessage());
            $this->message = 'Error crítico al cargar la lista de empleados.';
            $this->messageType = 'error';
            $this->showMessage = true;
            return view('livewire.empleados', [
                'empleados' => collect([]),
                'cargos' => collect([])
            ])->layout('layouts.app');
        }
    }
}