<div>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facturación - Moto Repuesto Divino Niño</title>
    <link rel="stylesheet" href="{{ asset('css/empleados.css') }}">
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
    @livewireStyles
    <style>
        .alert {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            border: 1px solid transparent;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
    </style>
</head>
<body>
<div class="container">
    <header class="main-header">
        <h1><i class='bx bx-user'></i> Gestión de Empleados</h1>
        <div class="header-actions">
            <button class="btn btn-primary" wire:click="openModal">
                <i class='bx bx-plus'></i> Nuevo Empleado
            </button>
        </div>
    </header>

    @if($showMessage)
        <div class="alert alert-{{ $messageType }}">
            <i class='bx bx-{{ $messageType == 'success' ? 'check-circle' : 'error-circle' }}'></i> {{ $message }}
        </div>
    @endif

    <div class="empleados-container">
        <div class="filtros-container">
            <div class="form-group">
                <input type="text" wire:model.live="search" placeholder="Buscar empleado...">
            </div>
            <div class="form-group">
                <select wire:model.live="filterCargo">
                    <option value="">Todos los cargos</option>
                    @foreach($cargos as $cargoOption)
                        <option value="{{ $cargoOption }}">{{ $cargoOption }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="table-container">
            <table class="empleados-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Cédula</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Cargo</th>
                        <th>Salario</th>
                        <th>Fecha Contratación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($empleados as $empleado)
                    <tr>
                        <td>{{ $empleado->id }}</td>
                        <td>{{ $empleado->nombre }}</td>
                        <td>{{ $empleado->apellido }}</td>
                        <td>{{ $empleado->cedula }}</td>
                        <td>{{ $empleado->telefono }}</td>
                        <td>{{ $empleado->email ?: '-' }}</td>
                        <td><span class="badge badge-{{ strtolower($empleado->cargo) }}">{{ $empleado->cargo }}</span></td>
                        <td>C$ {{ number_format($empleado->salario, 2) }}</td>
                        <td>{{ $empleado->fecha_contratacion ? $empleado->fecha_contratacion->format('d/m/Y') : '-' }}</td>
                        <td class="actions-cell">
                            <button class="btn btn-icon btn-primary" wire:click="edit({{ $empleado->id }})">
                                <i class='bx bx-edit'></i>
                            </button>
                            <button class="btn btn-icon btn-danger" wire:click="confirmDelete({{ $empleado->id }})">
                                <i class='bx bx-trash'></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center">No hay empleados activos registrados</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-container">
            {{ $empleados->links() }}
        </div>
    </div>

    @if($showModal)
    <div class="modal" style="display: flex;">
        <div class="modal-content">
            <span class="modal-close" wire:click="closeModal">×</span>
            <h2>
                <i class='bx bx-{{ $isEditing ? "edit" : "user-plus" }}'></i>
                {{ $isEditing ? 'Editar' : 'Nuevo' }} Empleado
            </h2>
            
            <form wire:submit.prevent="{{ $isEditing ? 'update' : 'save' }}">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nombre">Nombre *</label>
                        <input type="text" wire:model.defer="nombre" id="nombre" required
                               onkeypress="return /^[a-zA-Z\s]+$/.test(event.key)"
                               oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')">
                        @error('nombre') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="apellido">Apellido *</label>
                        <input type="text" wire:model.defer="apellido" id="apellido" required
                               onkeypress="return /^[a-zA-Z\s]+$/.test(event.key)"
                               oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')">
                        @error('apellido') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="cedula">Cédula *</label>
                        <input type="text" wire:model.defer="cedula" id="cedula" required
                               maxlength="14"
                               placeholder="000-000000-0000L"
                               onkeypress="return ((this.value.length < 13 && /^[0-9\-]+$/.test(event.key)) || (this.value.length === 13 && /^[a-zA-Z]+$/.test(event.key)));"
                               oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9\-]/g, '').substring(0, 14); if (this.value.length === 14 && !/^[A-Z]$/.test(this.value.charAt(13))) { this.value = this.value.substring(0, 13); } else if (this.value.length < 14) { this.value = this.value.replace(/[A-Z](?=[A-Z0-9\-]*$)/g, ''); }">
                        @error('cedula') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="telefono">Teléfono *</label>
                        <input type="tel" wire:model.defer="telefono" id="telefono" required
                               maxlength="8"
                               onkeypress="return /^[0-9]+$/.test(event.key)"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8)">
                        @error('telefono') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" wire:model.defer="email" id="email" required>
                        @error('email') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="direccion">Dirección *</label>
                        <textarea wire:model.defer="direccion" id="direccion" rows="2" required
                                  onkeypress="return /^[a-zA-Z0-9.,#\-_\s]+$/.test(event.key)"
                                  oninput="this.value = this.value.replace(/[^a-zA-Z0-9.,#\-_\s]/g, '')"></textarea>
                        @error('direccion') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="cargo">Cargo *</label>
                        <select wire:model.defer="cargo" id="cargo" required>
                            <option value="">Seleccione un cargo</option>
                            <option value="Vendedor">Vendedor</option>
                            <option value="Administrador">Administrador</option>
                            <option value="Inventario">Inventario</option>
                        </select>
                        @error('cargo') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="salario">Salario *</label>
                        <input type="number" wire:model.defer="salario" id="salario" step="0.01" min="0.01" required
                               onkeypress="return /^[0-9.]+$/.test(event.key) && (event.key !== '.' || this.value.indexOf('.') === -1)"
                               oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1')">
                        @error('salario') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="fecha_contratacion">Fecha de Contratación *</label>
                        <input type="date" wire:model.defer="fecha_contratacion" id="fecha_contratacion" required readonly>
                        @error('fecha_contratacion') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="usuario">Usuario *</label>
                        <input type="text" wire:model.defer="usuario" id="usuario" required
                               onkeypress="return /^[a-zA-Z0-9_.-]*$/.test(event.key)"
                               oninput="this.value = this.value.replace(/[^a-zA-Z0-9_.-]/g, '')">
                        @error('usuario') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="contraseña">Contraseña *</label>
                        <input type="password" wire:model.defer="contraseña" id="contraseña" required>
                        @error('contraseña') <span class="error">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">{{ $isEditing ? 'Actualizar' : 'Guardar' }}</button>
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
@endif

    @if($showConfirmModal)
    <div class="modal" style="display: flex;">
        <div class="modal-content" style="max-width: 400px;">
            <span class="modal-close" wire:click="closeConfirmModal">×</span>
            <h2><i class='bx bx-error-circle'></i> Confirmar Desactivación</h2>
            @if($showMessage)
                <div class="alert alert-{{ $messageType }}">
                    <i class='bx bx-{{ $messageType == 'success' ? 'check-circle' : 'error-circle' }}'></i> {{ $message }}
                </div>
            @endif
            @if(!$showMessage)
                <p>¿Está seguro de desactivar este empleado? No podrá iniciar sesión.</p>
            @endif
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" wire:click="closeConfirmModal">Cancelar</button>
                @if(!$showMessage)
                    <button type="button" class="btn btn-danger" wire:click="deleteConfirmed">
                        <i class='bx bx-trash'></i> Desactivar
                    </button>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
@livewireScripts
</body>
</html>
</div>