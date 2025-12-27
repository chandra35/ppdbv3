<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GtkSyncService;
use App\Repositories\GtkRepository;

class SyncGtkFromSimansa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gtk:sync 
                            {--force : Force sync even if GTK_SOURCE is local}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync GTK data from SIMANSA database to local database';

    protected GtkSyncService $syncService;
    protected GtkRepository $gtkRepository;

    /**
     * Create a new command instance.
     */
    public function __construct(GtkSyncService $syncService, GtkRepository $gtkRepository)
    {
        parent::__construct();
        $this->syncService = $syncService;
        $this->gtkRepository = $gtkRepository;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('===========================================');
        $this->info('   SYNC GTK FROM SIMANSA TO LOCAL');
        $this->info('===========================================');
        $this->newLine();

        // Check SIMANSA availability
        if (!$this->gtkRepository->isSimansaAvailable()) {
            $this->error('✗ Koneksi ke database SIMANSA tidak tersedia!');
            $this->warn('  Pastikan:');
            $this->warn('  1. Database SIMANSA sudah dikonfigurasi di .env');
            $this->warn('  2. Koneksi ke server SIMANSA dapat diakses');
            $this->newLine();
            return self::FAILURE;
        }

        $this->info('✓ Koneksi ke SIMANSA: OK');
        $this->newLine();

        // Check current source
        $currentSource = $this->gtkRepository->getSource();
        if ($currentSource === 'local' && !$this->option('force')) {
            $this->warn('⚠ GTK_SOURCE saat ini: LOCAL');
            $this->warn('  Gunakan --force untuk tetap melakukan sync');
            $this->newLine();
            
            if (!$this->confirm('Lanjutkan sync?', false)) {
                $this->info('Sync dibatalkan.');
                return self::SUCCESS;
            }
        }

        // Start sync
        $this->info('Memulai sinkronisasi...');
        $this->newLine();

        $bar = $this->output->createProgressBar();
        $bar->start();

        try {
            $result = $this->syncService->syncFromSimansa(function ($current, $total) use ($bar) {
                $bar->setMaxSteps($total);
                $bar->setProgress($current);
            });

            $bar->finish();
            $this->newLine(2);

            if ($result['success']) {
                $this->info('===========================================');
                $this->info('   SYNC SELESAI');
                $this->info('===========================================');
                $this->newLine();
                $this->line("<fg=green>✓ Berhasil sync:</fg=green>    {$result['synced_count']} GTK");
                $this->line("<fg=yellow>⟳ Diperbarui:</fg=yellow>       {$result['updated_count']} GTK");
                $this->line("<fg=red>✗ Error:</fg=red>          {$result['error_count']} GTK");
                $this->newLine();
                $this->info($result['message']);
                
                return self::SUCCESS;
            } else {
                $this->error('✗ Sync gagal: ' . $result['message']);
                return self::FAILURE;
            }

        } catch (\Exception $e) {
            $bar->finish();
            $this->newLine(2);
            $this->error('✗ Terjadi kesalahan: ' . $e->getMessage());
            $this->error('  Trace: ' . $e->getTraceAsString());
            return self::FAILURE;
        }
    }
}
