<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use ZipArchive;

class BackupController extends Controller
{
    /**
     * Display backup management page.
     */
    public function index()
    {
        $backups = $this->getBackupFiles();
        
        return view('admin.backup.index', compact('backups'));
    }

    /**
     * Create new backup (database + files).
     */
    public function create(Request $request)
    {
        try {
            $timestamp = now()->format('Y-m-d_His');
            $backupName = "backup_{$timestamp}";
            $backupPath = storage_path("app/backups/{$backupName}");
            
            // Create backup directory
            if (!file_exists($backupPath)) {
                mkdir($backupPath, 0755, true);
            }

            // 1. Backup Database
            $sqlFile = "{$backupPath}/database.sql";
            $this->backupDatabase($sqlFile);

            // 2. Copy important files/folders
            $this->backupFiles($backupPath);

            // 3. Create ZIP
            $zipFile = storage_path("app/backups/{$backupName}.zip");
            $this->createZip($backupPath, $zipFile);

            // 4. Delete temporary folder
            $this->deleteDirectory($backupPath);

            return redirect()
                ->route('admin.backup.index')
                ->with('success', "Backup berhasil dibuat: {$backupName}.zip");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal membuat backup: ' . $e->getMessage());
        }
    }

    /**
     * Download backup file.
     */
    public function download($filename)
    {
        $path = storage_path("app/backups/{$filename}");
        
        if (!file_exists($path)) {
            return redirect()
                ->route('admin.backup.index')
                ->with('error', 'File backup tidak ditemukan.');
        }

        return response()->download($path);
    }

    /**
     * Delete backup file.
     */
    public function destroy($filename)
    {
        try {
            $path = storage_path("app/backups/{$filename}");
            
            if (file_exists($path)) {
                unlink($path);
                return redirect()
                    ->route('admin.backup.index')
                    ->with('success', 'Backup berhasil dihapus.');
            }

            return redirect()
                ->route('admin.backup.index')
                ->with('error', 'File backup tidak ditemukan.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus backup: ' . $e->getMessage());
        }
    }

    /**
     * Get list of backup files.
     */
    private function getBackupFiles()
    {
        $backupPath = storage_path('app/backups');
        
        if (!file_exists($backupPath)) {
            mkdir($backupPath, 0755, true);
            return [];
        }

        $files = scandir($backupPath);
        $backups = [];

        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'zip') {
                $filePath = $backupPath . '/' . $file;
                $backups[] = [
                    'name' => $file,
                    'size' => $this->formatBytes(filesize($filePath)),
                    'date' => date('d/m/Y H:i', filemtime($filePath)),
                ];
            }
        }

        // Sort by date descending
        usort($backups, function($a, $b) {
            return strcmp($b['name'], $a['name']);
        });

        return $backups;
    }

    /**
     * Backup database to SQL file.
     */
    private function backupDatabase($sqlFile)
    {
        $host = config('database.connections.mysql.host');
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        // Using mysqldump command
        $command = sprintf(
            'mysqldump -h %s -u %s -p%s %s > %s',
            escapeshellarg($host),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($sqlFile)
        );

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new \Exception("Failed to backup database. Return code: {$returnVar}");
        }
    }

    /**
     * Backup important files.
     */
    private function backupFiles($backupPath)
    {
        // Backup dokumen_pendaftar folder
        $sourceDokumen = storage_path('app/dokumen_pendaftar');
        $destDokumen = $backupPath . '/dokumen_pendaftar';
        
        if (file_exists($sourceDokumen)) {
            $this->copyDirectory($sourceDokumen, $destDokumen);
        }

        // Backup public storage
        $sourcePublic = storage_path('app/public');
        $destPublic = $backupPath . '/public';
        
        if (file_exists($sourcePublic)) {
            $this->copyDirectory($sourcePublic, $destPublic);
        }
    }

    /**
     * Create ZIP archive.
     */
    private function createZip($source, $destination)
    {
        if (!extension_loaded('zip')) {
            throw new \Exception('ZIP extension is not loaded');
        }

        $zip = new ZipArchive();
        if (!$zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            throw new \Exception('Cannot create ZIP file');
        }

        $source = realpath($source);
        
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($source) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();
    }

    /**
     * Copy directory recursively.
     */
    private function copyDirectory($source, $destination)
    {
        if (!file_exists($destination)) {
            mkdir($destination, 0755, true);
        }

        $dir = opendir($source);
        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..') {
                $srcPath = $source . '/' . $file;
                $destPath = $destination . '/' . $file;
                
                if (is_dir($srcPath)) {
                    $this->copyDirectory($srcPath, $destPath);
                } else {
                    copy($srcPath, $destPath);
                }
            }
        }
        closedir($dir);
    }

    /**
     * Delete directory recursively.
     */
    private function deleteDirectory($dir)
    {
        if (!file_exists($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    /**
     * Format bytes to human readable size.
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
