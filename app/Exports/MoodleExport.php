<?php

namespace App\Exports;

use App\Models\JadwalUjian;
use App\Models\RuangUjian;
use App\Models\PesertaRuang;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MoodleExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    protected JadwalUjian $jadwalUjian;
    protected string $tahunShort;

    public function __construct(JadwalUjian $jadwalUjian, string $tahunShort)
    {
        $this->jadwalUjian = $jadwalUjian;
        $this->tahunShort = $tahunShort;
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
        $rows = [];
        $processedUsers = [];

        foreach ($this->jadwalUjian->sesiUjian as $sesi) {
            $cohort = 'ppdb' . $this->tahunShort . '_s' . $sesi->nomor_sesi;

            $ruangList = RuangUjian::where('sesi_ujian_id', $sesi->id)->get();

            foreach ($ruangList as $ruang) {
                $pesertaList = PesertaRuang::with(['calonSiswa.user'])
                    ->where('ruang_ujian_id', $ruang->id)
                    ->orderBy('nomor_urut')
                    ->get();

                foreach ($pesertaList as $pr) {
                    $cs = $pr->calonSiswa;
                    if (!$cs) continue;

                    $key = $cs->id . '_' . $sesi->nomor_sesi;
                    if (isset($processedUsers[$key])) continue;
                    $processedUsers[$key] = true;

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
            }
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
