<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            $type = $request->input('backup_type', 'full');
            $isDatabaseOnly = $type === 'database';
            $backupName = ($isDatabaseOnly ? "database_backup_{$timestamp}" : "backup_{$timestamp}");
            $backupPath = storage_path("app/backups/{$backupName}");
            
            // Create backup directory
            if (!file_exists($backupPath)) {
                mkdir($backupPath, 0755, true);
            }

            // 1. Backup Database
            $sqlFile = "{$backupPath}/database.sql";
            $this->backupDatabase($sqlFile);

            // 2. Copy important files/folders for full backup
            if (!$isDatabaseOnly) {
                $this->backupFiles($backupPath);
            }

            // 3. Create ZIP
            $zipFile = storage_path("app/backups/{$backupName}.zip");
            $this->createZip($backupPath, $zipFile);

            // 4. Delete temporary folder
            $this->deleteDirectory($backupPath);

            return redirect()
                ->route('admin.backup.index')
                ->with('success', ($isDatabaseOnly ? 'Backup database' : 'Backup lengkap') . " berhasil dibuat: {$backupName}.zip");
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
                    'type' => str_starts_with($file, 'database_backup_') ? 'Database' : 'Lengkap',
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
        if ($this->tryMysqlDump($sqlFile)) {
            return;
        }

        $this->backupDatabaseWithPhp($sqlFile);
    }

    private function tryMysqlDump(string $sqlFile): bool
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if (($config['driver'] ?? null) !== 'mysql') {
            return false;
        }

        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 3306;
        $database = $config['database'] ?? '';
        $username = $config['username'] ?? '';
        $password = $config['password'] ?? '';

        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s --skip-comments --single-transaction %s',
            escapeshellarg($host),
            escapeshellarg((string) $port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database)
        );

        $output = [];
        $returnVar = 1;
        exec($command . ' > ' . escapeshellarg($sqlFile) . ' 2>NUL', $output, $returnVar);

        return $returnVar === 0 && file_exists($sqlFile) && filesize($sqlFile) > 0;
    }

    private function backupDatabaseWithPhp(string $sqlFile): void
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        if ($driver !== 'mysql') {
            throw new \RuntimeException("Backup database otomatis saat ini hanya mendukung MySQL. Driver aktif: {$driver}");
        }

        $pdo = $connection->getPdo();
        $tables = collect(DB::select('SHOW TABLES'))
            ->map(fn ($row) => array_values((array) $row)[0])
            ->all();

        $handle = fopen($sqlFile, 'wb');

        if (!$handle) {
            throw new \RuntimeException('Tidak dapat membuat file SQL backup.');
        }

        fwrite($handle, "-- PPDBV3 database backup\n");
        fwrite($handle, "-- Generated at: " . now()->toDateTimeString() . "\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

        foreach ($tables as $table) {
            $createTable = DB::select("SHOW CREATE TABLE `{$table}`");
            $createSql = $createTable[0]->{'Create Table'} ?? null;

            if (!$createSql) {
                continue;
            }

            fwrite($handle, "-- Table: {$table}\n");
            fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");
            fwrite($handle, $createSql . ";\n\n");

            foreach (DB::table($table)->cursor() as $row) {
                $values = array_map(function ($value) use ($pdo) {
                    if ($value === null) {
                        return 'NULL';
                    }

                    if (is_bool($value)) {
                        return $value ? '1' : '0';
                    }

                    return $pdo->quote((string) $value);
                }, array_values((array) $row));

                $columns = array_map(fn ($column) => "`{$column}`", array_keys((array) $row));

                fwrite(
                    $handle,
                    sprintf(
                        "INSERT INTO `%s` (%s) VALUES (%s);\n",
                        $table,
                        implode(', ', $columns),
                        implode(', ', $values)
                    )
                );
            }

            fwrite($handle, "\n");
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);
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
