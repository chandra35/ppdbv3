<?php

namespace App\Exports;

use App\Exports\LaporanPpdbSheet;
use App\Exports\DataPendaftarSheet;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaporanPpdbExport implements WithMultipleSheets
{
    protected $stats;
    protected $selectedTahun;
    protected $selectedJalur;
    protected $selectedGelombang;
    protected $sekolah;
    protected ?Collection $pendaftar;
    protected ?Collection $nilaiSeleksiMap;
    protected ?Collection $nilaiCbtMap;

    public function __construct($stats, $selectedTahun, $selectedJalur, $selectedGelombang, $sekolah, ?Collection $pendaftar = null, ?Collection $nilaiSeleksiMap = null, ?Collection $nilaiCbtMap = null)
    {
        $this->stats = $stats;
        $this->selectedTahun = $selectedTahun;
        $this->selectedJalur = $selectedJalur;
        $this->selectedGelombang = $selectedGelombang;
        $this->sekolah = $sekolah;
        $this->pendaftar = $pendaftar;
        $this->nilaiSeleksiMap = $nilaiSeleksiMap;
        $this->nilaiCbtMap = $nilaiCbtMap;
    }

    public function sheets(): array
    {
        $sheets = [
            new LaporanPpdbSheet(
                'Ringkasan',
                $this->stats,
                $this->selectedTahun,
                $this->selectedJalur,
                $this->selectedGelombang,
                $this->sekolah,
                'ringkasan'
            ),
            new LaporanPpdbSheet(
                'Total Pendaftar',
                $this->stats,
                $this->selectedTahun,
                $this->selectedJalur,
                $this->selectedGelombang,
                $this->sekolah,
                'total_pendaftar'
            ),
            new LaporanPpdbSheet(
                'Dapat Nomor Tes',
                $this->stats,
                $this->selectedTahun,
                $this->selectedJalur,
                $this->selectedGelombang,
                $this->sekolah,
                'dengan_nomor_tes'
            ),
            new LaporanPpdbSheet(
                'Tanpa Nomor Tes',
                $this->stats,
                $this->selectedTahun,
                $this->selectedJalur,
                $this->selectedGelombang,
                $this->sekolah,
                'tanpa_nomor_tes'
            ),
            new LaporanPpdbSheet(
                'Peserta Tes',
                $this->stats,
                $this->selectedTahun,
                $this->selectedJalur,
                $this->selectedGelombang,
                $this->sekolah,
                'peserta_tes'
            ),
            new LaporanPpdbSheet(
                'Kelulusan',
                $this->stats,
                $this->selectedTahun,
                $this->selectedJalur,
                $this->selectedGelombang,
                $this->sekolah,
                'kelulusan'
            ),
            new LaporanPpdbSheet(
                'Sebaran Sekolah',
                $this->stats,
                $this->selectedTahun,
                $this->selectedJalur,
                $this->selectedGelombang,
                $this->sekolah,
                'sebaran_sekolah'
            ),
        ];

        // Tambah sheet data pendaftar jika collection tersedia
        if ($this->pendaftar !== null) {
            // Sheet: Semua Pendaftar
            $sheets[] = new DataPendaftarSheet(
                'Semua Pendaftar',
                $this->pendaftar,
                $this->selectedTahun,
                $this->selectedJalur,
                $this->selectedGelombang,
                $this->sekolah,
                $this->nilaiSeleksiMap,
                $this->nilaiCbtMap
            );

            // Sheet: Pendaftar Dengan Nomor Tes
            $denganNomorTes = $this->pendaftar->filter(fn($p) => !empty($p->nomor_tes));
            $sheets[] = new DataPendaftarSheet(
                'Data Punya Nomor Tes',
                $denganNomorTes,
                $this->selectedTahun,
                $this->selectedJalur,
                $this->selectedGelombang,
                $this->sekolah,
                $this->nilaiSeleksiMap,
                $this->nilaiCbtMap
            );
        }

        return $sheets;
    }
}
