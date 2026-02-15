<?php

namespace App\Exports;

use App\Models\CalonSiswa;
use App\Models\TahunPelajaran;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Export Moodle-compatible XLSX for pendaftar yang sudah punya nomor tes
 * tapi belum masuk ke jadwal/sesi ujian (mendapat nomor tes di hari H)
 */
class MoodlePendaftarExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    protected string $tahunShort;
    protected ?string $jalurId;
    protected ?string $gelombangId;

    public function __construct(string $tahunShort, ?string $jalurId = null, ?string $gelombangId = null)
    {
        $this->tahunShort = $tahunShort;
        $this->jalurId = $jalurId;
        $this->gelombangId = $gelombangId;
    }

    public function headings(): array
    {
        return [
            'firstname',
            'lastname',
            'username',
            'password',
            'email',
            'cohort1',
        ];
    }

    public function array(): array
    {
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();

        $query = CalonSiswa::with('user')
            ->where('is_finalisasi', true)
            ->whereNotNull('nomor_tes')
            ->where('nomor_tes', '!=', '');

        if ($tahunAktif) {
            $query->where('tahun_pelajaran_id', $tahunAktif->id);
        }

        if ($this->jalurId) {
            $query->where('jalur_pendaftaran_id', $this->jalurId);
        }

        if ($this->gelombangId) {
            $query->where('gelombang_pendaftaran_id', $this->gelombangId);
        }

        $pendaftar = $query->orderBy('nomor_tes')->get();

        $cohort = 'ppdb' . $this->tahunShort;

        $rows = [];
        foreach ($pendaftar as $cs) {
            $user = $cs->user;
            $password = $user?->readable_password ?? ($cs->nisn ?? 'ppdb' . $this->tahunShort);

            $rows[] = [
                $cs->nama_lengkap ?? '',
                'PPDB ' . $this->tahunShort,
                $cs->nisn ?? '',
                $password,
                $cs->email ?? ($cs->nisn . '@ppdb.local'),
                $cohort,
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9E1F2'],
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30, // firstname
            'B' => 15, // lastname
            'C' => 15, // username
            'D' => 15, // password
            'E' => 30, // email
            'F' => 20, // cohort1
        ];
    }
}
