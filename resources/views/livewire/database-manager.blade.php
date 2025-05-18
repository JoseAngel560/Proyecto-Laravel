<div class="indicator-widget db-widget">

    @if($notification)
        <div class="notification {{ $notificationType }}" wire:poll.1000ms="$set('notification', null)">
            {{ $notification }}
        </div>
    @endif

    <form wire:submit.prevent="downloadBackup">
        <div class="db-action">
            <label>Respaldar Base de Datos</label>
            <div class="db-controls">
                <button type="submit" class="btn-db" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="downloadBackup">Crear Respaldo</span>
                    <span wire:loading wire:target="downloadBackup">Procesando...</span>
                </button>
            </div>
        </div>
    </form>

    <form wire:submit.prevent="restoreDatabase" enctype="multipart/form-data" class="db-restore-form">
    <div class="db-action">
        <label>Restaurar Base de Datos</label>
        <div class="db-controls">
            <input type="file" wire:model="backupFile" id="backupFileInput" accept=".bak,.sql" style="display:none;">
            <div class="input-group">
                <input type="text" 
                       placeholder="{{ $backupFile ? $backupFile->getClientOriginalName() : 'Selecciona archivo .bak o .sql' }}" 
                       readonly 
                       onclick="document.getElementById('backupFileInput').click()"
                       class="file-name-input">
                <button type="submit" class="btn-db btn-restore" {{ $backupFile ? '' : 'disabled' }} wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="restoreDatabase">Restaurar</span>
                    <span wire:loading wire:target="restoreDatabase">Procesando...</span>
                </button>
            </div>
        </div>
        @error('backupFile') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>
</form>
</div>