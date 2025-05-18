<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatabaseManager extends Component
{
    use WithFileUploads;

    public $backupFile;
    public $notification = null;
    public $notificationType = '';
    public $lastBackupFilename;

    // Define las reglas de validación para el archivo de respaldo
    protected function rules()
    {
        return [
            'backupFile' => 'required|file|mimes:bak,sql|max:50000', // 50MB máximo
        ];
    }

    // Renderiza la vista del componente
    public function render()
    {
        return view('livewire.database-manager');
    }

    // Genera y descarga un respaldo de la base de datos
    public function downloadBackup()
    {
        try {
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');

            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $this->lastBackupFilename = $filename;

            $path = storage_path('app/backups');

            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            } else {
                chmod($path, 0777);
            }

            $fullPath = $path . '/' . $filename;

            // Ruta absoluta de mysqldump en Windows Laragon (ajusta si usas otro SO)
            $mysqldumpPath = 'C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysqldump.exe';

            $command = escapeshellcmd($mysqldumpPath);
            $args = [
                '--user=' . escapeshellarg($username),
                '--host=' . escapeshellarg($host),
            ];

            if ($password) {
                $args[] = '--password=' . escapeshellarg($password);
            }

            $args[] = escapeshellarg($database);

            $fullCommand = $command . ' ' . implode(' ', $args) . ' > ' . escapeshellarg($fullPath) . ' 2>&1';

            exec($fullCommand, $output, $returnVar);

            if ($returnVar !== 0) {
                $errorMessage = implode("\n", $output);
                $this->showNotification("Error al generar el respaldo: $errorMessage", 'error');
                return;
            }

            if (!file_exists($fullPath) || filesize($fullPath) == 0) {
                $this->showNotification("El archivo de respaldo no se creó correctamente.", 'error');
                return;
            }

            // Retornar la descarga directamente sin JS
            return response()->download($fullPath)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            $this->showNotification('Error: ' . $e->getMessage(), 'error');
        }
    }

    // Restaura la base de datos desde un archivo de respaldo
    public function restoreDatabase()
    {
        try {
            $this->validate();

            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');

            $tempPath = $this->backupFile->getRealPath();

            $mysqlPath = 'C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysql.exe';

            $command = escapeshellcmd($mysqlPath);
            $args = [
                '--user=' . escapeshellarg($username),
                '--host=' . escapeshellarg($host),
            ];

            if ($password) {
                $args[] = '--password=' . escapeshellarg($password);
            }

            $args[] = escapeshellarg($database);

            $fullCommand = $command . ' ' . implode(' ', $args) . ' < ' . escapeshellarg($tempPath) . ' 2>&1';

            exec($fullCommand, $output, $returnVar);

            if ($returnVar !== 0) {
                $errorMessage = implode("\n", $output);
                $this->showNotification("Error al restaurar la base de datos: $errorMessage", 'error');
                return;
            }

            $this->reset(['backupFile']);
            $this->showNotification('Base de datos restaurada exitosamente.', 'success');

        } catch (\Exception $e) {
            $this->showNotification('Error: ' . $e->getMessage(), 'error');
        }
    }

    // Muestra una notificación con un mensaje y tipo específicos
    private function showNotification($message, $type)
    {
        $this->notification = $message;
        $this->notificationType = $type;

        $this->dispatch('showNotification');
    }
}