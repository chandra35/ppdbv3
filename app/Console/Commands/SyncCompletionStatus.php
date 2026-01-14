<?php

namespace App\Console\Commands;

use App\Models\CalonSiswa;
use Illuminate\Console\Command;

class SyncCompletionStatus extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ppdb:sync-completion 
                            {--id= : Sync specific calon siswa by ID}
                            {--dry-run : Show what would be changed without saving}';

    /**
     * The console command description.
     */
    protected $description = 'Sync completion status (data_diri, data_ortu, data_dokumen) based on actual data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $specificId = $this->option('id');

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be saved');
        }

        $query = CalonSiswa::query();
        
        if ($specificId) {
            $query->where('id', $specificId);
        }

        $pendaftars = $query->get();
        $total = $pendaftars->count();

        if ($total === 0) {
            $this->error('No records found!');
            return 1;
        }

        $this->info("📋 Processing {$total} pendaftar(s)...");
        $this->newLine();

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $stats = [
            'unchanged' => 0,
            'updated' => 0,
            'details' => []
        ];

        foreach ($pendaftars as $pendaftar) {
            $before = [
                'data_diri' => $pendaftar->data_diri_completed,
                'data_ortu' => $pendaftar->data_ortu_completed,
                'data_dokumen' => $pendaftar->data_dokumen_completed,
            ];

            $newDataDiri = $pendaftar->checkDataDiriComplete();
            $newDataOrtu = $pendaftar->checkDataOrtuComplete();
            $newDataDokumen = $pendaftar->checkDataDokumenComplete();

            $changed = $before['data_diri'] !== $newDataDiri 
                || $before['data_ortu'] !== $newDataOrtu
                || $before['data_dokumen'] !== $newDataDokumen;

            if ($changed) {
                $stats['updated']++;
                $stats['details'][] = [
                    'id' => $pendaftar->id,
                    'nama' => $pendaftar->nama_lengkap,
                    'before' => $before,
                    'after' => [
                        'data_diri' => $newDataDiri,
                        'data_ortu' => $newDataOrtu,
                        'data_dokumen' => $newDataDokumen,
                    ]
                ];

                if (!$dryRun) {
                    $pendaftar->data_diri_completed = $newDataDiri;
                    $pendaftar->data_ortu_completed = $newDataOrtu;
                    $pendaftar->data_dokumen_completed = $newDataDokumen;
                    $pendaftar->save();
                }
            } else {
                $stats['unchanged']++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Show summary
        $this->info('📊 Summary:');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Processed', $total],
                ['Updated', $stats['updated']],
                ['Unchanged', $stats['unchanged']],
            ]
        );

        // Show details of changes
        if (!empty($stats['details'])) {
            $this->newLine();
            $this->info('📝 Changed Records:');
            
            $tableData = [];
            foreach ($stats['details'] as $detail) {
                $tableData[] = [
                    substr($detail['id'], 0, 8) . '...',
                    $detail['nama'],
                    $this->formatStatus($detail['before']['data_diri']) . ' → ' . $this->formatStatus($detail['after']['data_diri']),
                    $this->formatStatus($detail['before']['data_ortu']) . ' → ' . $this->formatStatus($detail['after']['data_ortu']),
                    $this->formatStatus($detail['before']['data_dokumen']) . ' → ' . $this->formatStatus($detail['after']['data_dokumen']),
                ];
            }

            $this->table(
                ['ID', 'Nama', 'Data Diri', 'Data Ortu', 'Dokumen'],
                $tableData
            );
        }

        if ($dryRun) {
            $this->newLine();
            $this->warn('⚠️  This was a dry run. Run without --dry-run to apply changes.');
        }

        $this->newLine();
        $this->info('✅ Done!');

        return 0;
    }

    private function formatStatus(bool $status): string
    {
        return $status ? '✓' : '✗';
    }
}
