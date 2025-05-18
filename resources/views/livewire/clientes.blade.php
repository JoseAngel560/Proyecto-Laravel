<div>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Clientes - Moto Repuesto Divino Niño</title>
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}">
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
        <h1><i class='bx bx-user'></i> Gestión de Clientes</h1>
        <div class="header-actions">
            <button class="btn btn-primary" wire:click="openModal">
                <i class='bx bx-plus'></i> Nuevo Cliente
            </button>
        </div>
    </header>

    @if($showMessage)
        <div class="alert alert-{{ $messageType }}">
            <i class='bx bx-{{ $messageType == 'success' ? 'check-circle' : 'error-circle' }}'></i> {{ $message }}
        </div>
    @endif

    <div class="clientes-container">
        <div class="filtros-container">
            <div class="form-group">
                <input type="text" wire:model.live="search" placeholder="Buscar cliente..."
                       onkeypress="return /^[a-zA-Z0-9\s]+$/.test(event.key)"
                       oninput="this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, '')">
            </div>
        </div>

        <div class="table-container">
            <table class="clientes-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Teléfono</th>
                        <th>Dirección</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clientes as $cliente)
                    <tr>
                        <td>{{ $cliente->id }}</td>
                        <td>{{ $cliente->nombre }}</td>
                        <td>{{ $cliente->apellido }}</td>
                        <td>{{ $cliente->telefono }}</td>
                        <td>{{ $cliente->direccion ?: '-' }}</td>
                        <td class="actions-cell">
                            <button class="btn btn-icon btn-primary" wire:click="edit({{ $cliente->id }})">
                                <i class='bx bx-edit'></i>
                            </button>
                            <button class="btn btn-icon btn-danger" wire:click="confirmDelete({{ $cliente->id }})">
                                <i class='bx bx-trash'></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No hay clientes activos registrados</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-container">
            {{ $clientes->links() }}
        </div>
    </div>

    @if($showModal)
    <div class="modal" style="display: flex;">
        <div class="modal-content">
            <span class="modal-close" wire:click="closeModal">×</span>
            <h2>
                <i class='bx bx-{{ $isEditing ? "edit" : "user-plus" }}'></i>
                {{ $isEditing ? 'Editar' : 'Nuevo' }} Cliente
            </h2>
            
            <form wire:submit.prevent="{{ $isEditing ? 'update' : 'save' }}">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nombre">Nombre*</label>
                        <input type="text" wire:model.defer="nombre" id="nombre" required
                            onkeypress="return /^[a-zA-Z\s]$/.test(event.key)"
                            oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')">
                        @error('nombre') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="apellido">Apellido*</label>
                        <input type="text" wire:model.defer="apellido" id="apellido" required
                            onkeypress="return /^[a-zA-Z\s]$/.test(event.key)"
                            oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')">
                        @error('apellido') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="telefono">Teléfono*</label>
                        <input type="tel" wire:model.defer="telefono" id="telefono" required
                               onkeypress="return /^[0-9]+$/.test(event.key)"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8)">
                        @error('telefono') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="direccion">Dirección (Opcional)</label>
                        <textarea wire:model.defer="direccion" id="direccion" rows="2" placeholder="Opcional"
                                  onkeypress="return /^[a-zA-Z0-9\s]+$/.test(event.key)"
                                  oninput="this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, '')"></textarea>
                        @error('direccion') <span class="error">{{ $message }}</span> @enderror
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
            <h2><i class='bx bx-error-circle'></i> Confirmar Acción</h2>
            <p>¿Está seguro de marcar este cliente como inactivo? Esto evitará que aparezca en la lista de clientes activos.</p>
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" wire:click="closeConfirmModal">Cancelar</button>
                <button type="button" class="btn btn-danger" wire:click="deactivateCliente">
                    <i class='bx bx-trash'></i> Marcar como Inactivo
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
@livewireScripts
</body>
</html>
</div>
