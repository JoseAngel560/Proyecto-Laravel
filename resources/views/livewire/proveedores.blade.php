<div>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Proveedores - Moto Repuesto Divino Niño</title>
    <link rel="stylesheet" href="{{ asset('css/proveedores.css') }}">
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
        <h1><i class='bx bx-store'></i> Gestión de Proveedores</h1>
        <div class="header-actions">
            <button class="btn btn-primary" wire:click="openModal">
                <i class='bx bx-plus'></i> Nuevo Proveedor
            </button>
        </div>
    </header>

    @if($showMessage)
        <div class="alert alert-{{ $messageType }}">
            <i class='bx bx-{{ $messageType == 'success' ? 'check-circle' : 'error-circle' }}'></i> {{ $message }}
        </div>
    @endif

    <div class="proveedores-container">
        <div class="filtros-container">
            <div class="form-group">
                <input type="text" wire:model.live="search" placeholder="Buscar proveedor...">
            </div>
        </div>

        <div class="table-container">
            <table class="proveedores-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Razón Social</th>
                        <th>Contacto</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>RUC</th>
                        <th>Fecha Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($proveedores as $proveedor)
                    <tr>
                        <td>{{ $proveedor->id }}</td>
                        <td>{{ $proveedor->nombre }}</td>
                        <td>{{ $proveedor->razon_social }}</td>
                        <td>{{ $proveedor->contacto }}</td>
                        <td>{{ $proveedor->telefono }}</td>
                        <td>{{ $proveedor->email }}</td>
                        <td>{{ $proveedor->ruc }}</td>
                        <td>{{ $proveedor->fecha_registro ? $proveedor->fecha_registro->format('d/m/Y') : '-' }}</td>
                        <td class="actions-cell">
                            <button class="btn btn-icon btn-primary" wire:click="edit({{ $proveedor->id }})">
                                <i class='bx bx-edit'></i>
                            </button>
                            <button class="btn btn-icon btn-danger" wire:click="confirmDelete({{ $proveedor->id }})">
                                <i class='bx bx-trash'></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">No hay proveedores activos registrados</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-container">
            {{ $proveedores->links() }}
        </div>
    </div>

    @if($showModal)
    <div class="modal" style="display: flex;">
        <div class="modal-content">
            <span class="modal-close" wire:click="closeModal">×</span>
            <h2>
                <i class='bx bx-{{ $isEditing ? "edit" : "user-plus" }}'></i>
                {{ $isEditing ? 'Editar' : 'Nuevo' }} Proveedor
            </h2>
            
            <form wire:submit.prevent="{{ $isEditing ? 'update' : 'save' }}">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nombre">Nombre*</label>
                        <input type="text" wire:model.defer="nombre" id="nombre" required
                               onkeypress="return /^[a-zA-Z.\s]$/.test(event.key)"
                               oninput="this.value = this.value.replace(/[^a-zA-Z.\s]/g, '')">
                        @error('nombre') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="razon_social">Razón Social*</label>
                        <input type="text" wire:model.defer="razon_social" id="razon_social" required
                               onkeypress="return /^[a-zA-Z.\s]$/.test(event.key)"
                               oninput="this.value = this.value.replace(/[^a-zA-Z.\s]/g, '')">
                        @error('razon_social') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="contacto">Contacto*</label>
                        <input type="text" wire:model.defer="contacto" id="contacto" required
                               onkeypress="return /^[a-zA-Z\s]$/.test(event.key)"
                               oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')">
                        @error('contacto') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="telefono">Teléfono*</label>
                        <input type="tel" wire:model.defer="telefono" id="telefono" required
                               onkeypress="return /^[0-9]$/.test(event.key)"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8)">
                        @error('telefono') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="email">Email*</label>
                        <input type="email" wire:model.defer="email" id="email" required>
                        @error('email') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="direccion">Dirección*</label>
                        <textarea wire:model.defer="direccion" id="direccion" rows="2" required
                                  onkeypress="return /^[a-zA-Z0-9.,#\-_\s]$/.test(event.key)"
                                  oninput="this.value = this.value.replace(/[^a-zA-Z0-9.,#\-_\s]/g, '')"></textarea>
                        @error('direccion') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="ruc">RUC*</label>
                        <input type="text" wire:model.defer="ruc" id="ruc" required
                               onkeypress="return /^[0-9]+$/.test(event.key)"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 14)">
                        @error('ruc') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="fecha_registro">Fecha de Registro*</label>
                        <input type="date" wire:model.defer="fecha_registro" id="fecha_registro" required readonly>
                        @error('fecha_registro') <span class="error">{{ $message }}</span> @enderror
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
            <p>¿Está seguro de marcar este proveedor como inactivo? Esto evitará que aparezca en la lista de proveedores activos.</p>
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" wire:click="closeConfirmModal">Cancelar</button>
                <button type="button" class="btn btn-danger" wire:click="deactivateProveedor">
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
