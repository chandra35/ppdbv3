<?php

namespace App\Exports;

use App\Models\Registrasi;

/**
 * Export data pendaftar yang SUDAH REGISTRASI / BAYAR.
 *
 * Mewarisi seluruh kolom lengkap dari PendaftarExport (identik dengan
 * export Data Pendaftar), lalu menyisipkan kolom khusus registrasi
 * tepat setelah kolom "No": bukti bayar (notes), tanggal registrasi,
 * status pencocokan, serta jejak perpindahan jurusan (awal -> final).
 */
class RegistrasiExport extends PendaftarExport
{
    public function __construct($tahunPelajaranId = null, $jalurId = null, $gelombangId = null)
    {
        parent::__construct('all', $tahunPelajaranId, $jalurId, $gelombangId);
    }

    public function collection()
    {
        $query = Registrasi::with([
            'calonSiswa.ortu',
            'calonSiswa.nilaiRapor',
            'calonSiswa.jalurPendaftaran',
            'calonSiswa.gelombangPendaftaran',
            'calonSiswa.provinsiSiswa',
            'calonSiswa.kabupatenSiswa',
            'calonSiswa.kecamatanSiswa',
            'calonSiswa.kelurahanSiswa',
            'calonSiswa.user',
        ]);

        if ($this->tahunPelajaranId) {
            $query->where('tahun_pelajaran_id', $this->tahunPelajaranId);
        }

        $regs = $query->get();

        return $regs->map(function ($reg) {
            $cs = $reg->calonSiswa;
            if (!$cs) {
                return null;
            }

            if ($this->jalurId && $cs->jalur_pendaftaran_id !== $this->jalurId) {
                return null;
            }
            if ($this->gelombangId && $cs->gelombang_pendaftaran_id !== $this->gelombangId) {
                return null;
            }

            // Sematkan data registrasi ke model agar dapat dibaca di map().
            $cs->reg_data = $reg;

            return $cs;
        })->filter()->sortBy('nama_lengkap')->values();
    }

    public function headings(): array
    {
        $base = parent::headings();
        $regCols = [
            'Bukti Bayar (Notes)',
            'Tgl Registrasi',
            'Status Pencocokan',
            'Jurusan Awal',
            'Jurusan Final',
            'Pindah Jurusan?',
        ];
        array_splice($base, 1, 0, $regCols);

        return $base;
    }

    public function map($pendaftar): array
    {
        $base = parent::map($pendaftar);

        /** @var \App\Models\Registrasi|null $reg */
        $reg = $pendaftar->reg_data ?? null;

        $regCols = [
            $reg?->notes ?? '',
            $reg?->tanggal_registrasi ? $reg->tanggal_registrasi->format('d/m/Y H:i') : '',
            $this->formatMatchStatus($reg?->match_status),
            $reg?->jurusan_awal ?? '',
            $reg?->jurusan_final ?? '',
            ($reg && $reg->pindah_jurusan) ? 'Ya' : 'Tidak',
        ];
        array_splice($base, 1, 0, $regCols);

        return $base;
    }

    public function title(): string
    {
        return 'Sudah Registrasi';
    }

    private function formatMatchStatus($status): string
    {
        return match ($status) {
            'matched_exact' => 'Cocok Persis',
            'matched_fuzzy' => 'Mirip',
            'conflict_jurusan' => 'Konflik Jurusan',
            'manual' => 'Manual',
            'unmatched' => 'Tidak Cocok',
            default => $status ?? '',
        };
    }
}
