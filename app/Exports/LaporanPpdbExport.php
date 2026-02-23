<?php

namespace App\Exports;

use App\Exports\LaporanPpdbSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaporanPpdbExport implements WithMultipleSheets
{
    protected $stats;
    protected $selectedTahun;
    protected $selectedJalur;
    protected $selectedGelombang;
    protected $sekolah;

    public function __construct($stats, $selectedTahun, $selectedJalur, $selectedGelombang, $sekolah)
    {
        $this->stats = $stats;
        $this->selectedTahun = $selectedTahun;
        $this->selectedJalur = $selectedJalur;
        $this->selectedGelombang = $selectedGelombang;
        $this->sekolah = $sekolah;
    }

    public function sheets(): array
    {
        return [
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
    }
}
