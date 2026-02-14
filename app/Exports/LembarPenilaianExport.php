<?php

namespace App\Exports;

use App\Models\JadwalUjian;
use App\Models\RuangUjian;
use App\Models\PesertaRuang;
use App\Models\BobotNilaiSeleksi;
use App\Models\NilaiSeleksi;
use App\Models\SekolahSettings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LembarPenilaianExport implements WithMultipleSheets
{
    protected JadwalUjian $jadwalUjian;

    public function __construct(JadwalUjian $jadwalUjian)
    {
        $this->jadwalUjian = $jadwalUjian;
    }

    public function sheets(): array
    {
        $sheets = [];
        $jadwal = $this->jadwalUjian->load(['tahunPelajaran', 'sesiUjian']);
        $sekolah = SekolahSettings::getSettings();

        // Get active bobot for this tahun pelajaran
        $bobotList = BobotNilaiSeleksi::active()
            ->forTahun($jadwal->tahun_pelajaran_id)
            ->orderBy('urutan')
            ->get();

        foreach ($jadwal->sesiUjian as $sesi) {
            $ruangList = RuangUjian::where('sesi_ujian_id', $sesi->id)->orderBy('nama_ruang')->get();

            foreach ($ruangList as $ruang) {
                $pesertaRuang = PesertaRuang::with('calonSiswa')
                    ->where('ruang_ujian_id', $ruang->id)
                    ->orderBy('nomor_urut')
                    ->get();

                if ($pesertaRuang->isEmpty()) continue;

                // Load nilai for each peserta
                $nilaiMap = NilaiSeleksi::where('ruang_ujian_id', $ruang->id)
                    ->get()
                    ->keyBy('calon_siswa_id');

                $sheetTitle = $ruang->nama_ruang;
                // Excel sheet names max 31 chars, no special chars
                $sheetTitle = substr(preg_replace('/[^\w\s\-]/', '', $sheetTitle), 0, 31);

                $sheets[] = new LembarPenilaianSheet(
                    $jadwal,
                    $sesi,
                    $ruang,
                    $pesertaRuang,
                    $bobotList,
                    $nilaiMap,
                    $sekolah
                );
            }
        }

        return $sheets;
    }
}
