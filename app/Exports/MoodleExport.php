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
    protected int $cohortCount = 1;

    public function __construct(JadwalUjian $jadwalUjian, string $tahunShort)
    {
        $this->jadwalUjian = $jadwalUjian;
        $this->tahunShort = $tahunShort;
    }

    public function headings(): array
    {
        $headers = [
            'firstname',
            'lastname',
            'username',
            'password',
            'email',
        ];

        for ($i = 1; $i <= $this->cohortCount; $i++) {
            $headers[] = 'cohort' . $i;
        }

        return $headers;
    }

    public function array(): array
    {
        $studentData = $this->buildStudentData();

        $rows = [];
        foreach ($studentData as $data) {
            $row = [
                $data['firstname'],
                $data['lastname'],
                $data['username'],
                $data['password'],
                $data['email'],
            ];
            // Pad cohorts to match cohortCount columns
            for ($i = 0; $i < $this->cohortCount; $i++) {
                $row[] = $data['cohorts'][$i] ?? '';
            }
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Build unique student data with all their cohorts collected
     */
    protected function buildStudentData(): array
    {
        $students = []; // keyed by calon_siswa_id

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

                    if (!isset($students[$cs->id])) {
                        $user = $cs->user;
                        $password = $user?->readable_password ?? ($cs->nisn ?? 'ppdb' . $this->tahunShort);

                        $students[$cs->id] = [
                            'firstname' => $cs->nama_lengkap ?? '',
                            'lastname' => 'PPDB ' . $this->tahunShort,
                            'username' => $cs->nisn ?? '',
                            'password' => $password,
                            'email' => $cs->email ?? ($cs->nisn . '@ppdb.local'),
                            'cohorts' => [],
                        ];
                    }

                    // Add cohort if not already added
                    if (!in_array($cohort, $students[$cs->id]['cohorts'])) {
                        $students[$cs->id]['cohorts'][] = $cohort;
                    }
                }
            }
        }

        // Determine max cohort count
        $this->cohortCount = max(1, ...array_map(fn($s) => count($s['cohorts']), $students));

        return array_values($students);
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
