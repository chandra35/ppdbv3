<?php

namespace App\Exports;

use App\Models\CalonSiswa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PendaftarExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, ShouldAutoSize
{
    protected $type; // 'all' or 'with_nomor_tes'
    protected $jalurId;
    protected $gelombangId;

    public function __construct($type = 'all', $jalurId = null, $gelombangId = null)
    {
        $this->type = $type;
        $this->jalurId = $jalurId;
        $this->gelombangId = $gelombangId;
    }

    public function collection()
    {
        $query = CalonSiswa::with([
            'ortu', 
            'nilaiRapor', 
            'jalurPendaftaran', 
            'gelombangPendaftaran',
            'provinsiSiswa',
            'kabupatenSiswa',
            'kecamatanSiswa',
            'kelurahanSiswa',
            'user'
        ]);

        if ($this->type === 'with_nomor_tes') {
            $query->whereNotNull('nomor_tes')->where('nomor_tes', '!=', '');
        }

        if ($this->jalurId) {
            $query->where('jalur_pendaftaran_id', $this->jalurId);
        }

        if ($this->gelombangId) {
            $query->where('gelombang_pendaftaran_id', $this->gelombangId);
        }

        return $query->orderBy('nama_lengkap')->get();
    }

    public function headings(): array
    {
        return [
            // Data Diri
            'No',
            'Nomor Tes',
            'Password',
            'No Registrasi',
            'NISN',
            'NIK',
            'Nama Lengkap',
            'Jenis Kelamin',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Agama',
            'Anak Ke',
            'Jumlah Saudara',
            
            // Alamat Siswa
            'Alamat Siswa',
            'RT',
            'RW',
            'Kelurahan',
            'Kecamatan',
            'Kabupaten',
            'Provinsi',
            'Kode Pos',
            
            // Kontak & Sekolah Asal
            'Nomor HP',
            'Email',
            'NPSN Asal',
            'Nama Sekolah Asal',
            
            // Jalur & Gelombang
            'Jalur Pendaftaran',
            'Gelombang',
            'Pilihan Program',
            
            // Status
            'Status Verifikasi',
            'Status Admisi',
            'Finalisasi',
            'Tanggal Registrasi',
            
            // Data Orang Tua - Ayah
            'No KK',
            'Status Ayah',
            'NIK Ayah',
            'Nama Ayah',
            'Tempat Lahir Ayah',
            'Tanggal Lahir Ayah',
            'Pendidikan Ayah',
            'Pekerjaan Ayah',
            'Penghasilan Ayah',
            'HP Ayah',
            
            // Data Orang Tua - Ibu
            'Status Ibu',
            'NIK Ibu',
            'Nama Ibu',
            'Tempat Lahir Ibu',
            'Tanggal Lahir Ibu',
            'Pendidikan Ibu',
            'Pekerjaan Ibu',
            'Penghasilan Ibu',
            'HP Ibu',
            
            // Data Wali
            'Tinggal Dengan Wali',
            'Nama Wali',
            'Hubungan Wali',
            'NIK Wali',
            'Pendidikan Wali',
            'Pekerjaan Wali',
            'Penghasilan Wali',
            'HP Wali',
            
            // Alamat Orang Tua
            'Alamat Orang Tua',
            
            // Nilai Rapor Semester 1-5
            'MTK Smt 1',
            'IPA Smt 1',
            'IPS Smt 1',
            'Rata-rata Smt 1',
            'MTK Smt 2',
            'IPA Smt 2',
            'IPS Smt 2',
            'Rata-rata Smt 2',
            'MTK Smt 3',
            'IPA Smt 3',
            'IPS Smt 3',
            'Rata-rata Smt 3',
            'MTK Smt 4',
            'IPA Smt 4',
            'IPS Smt 4',
            'Rata-rata Smt 4',
            'MTK Smt 5',
            'IPA Smt 5',
            'IPS Smt 5',
            'Rata-rata Smt 5',
            
            // Nilai Akhir
            'Nilai CBT',
            'Nilai TBQ',
            'Nilai Akhir',
            'Ranking',
        ];
    }

    public function map($pendaftar): array
    {
        static $no = 0;
        $no++;

        $ortu = $pendaftar->ortu;
        
        // Get nilai per semester
        $nilai = [];
        for ($i = 1; $i <= 5; $i++) {
            $nilaiSemester = $pendaftar->nilaiRapor->where('semester', $i)->first();
            $nilai[$i] = [
                'mtk' => $nilaiSemester?->matematika ?? '',
                'ipa' => $nilaiSemester?->ipa ?? '',
                'ips' => $nilaiSemester?->ips ?? '',
                'rata' => $nilaiSemester?->rata_rata ?? '',
            ];
        }

        return [
            // Data Diri
            $no,
            $pendaftar->nomor_tes ?? '',
            $pendaftar->user?->readable_password ?? '',
            $pendaftar->nomor_registrasi ?? '',
            $pendaftar->nisn ?? '',
            $pendaftar->nik ?? '',
            $pendaftar->nama_lengkap ?? '',
            $pendaftar->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
            $pendaftar->tempat_lahir ?? '',
            $pendaftar->tanggal_lahir ? $pendaftar->tanggal_lahir->format('d/m/Y') : '',
            $pendaftar->agama ?? '',
            $pendaftar->anak_ke ?? '',
            $pendaftar->jumlah_saudara ?? '',
            
            // Alamat Siswa
            $pendaftar->alamat_siswa ?? '',
            $pendaftar->rt_siswa ?? '',
            $pendaftar->rw_siswa ?? '',
            $pendaftar->kelurahanSiswa?->name ?? '',
            $pendaftar->kecamatanSiswa?->name ?? '',
            $pendaftar->kabupatenSiswa?->name ?? '',
            $pendaftar->provinsiSiswa?->name ?? '',
            $pendaftar->kodepos_siswa ?? '',
            
            // Kontak & Sekolah Asal
            $pendaftar->nomor_hp ?? '',
            $pendaftar->email ?? '',
            $pendaftar->npsn_asal_sekolah ?? '',
            $pendaftar->nama_sekolah_asal ?? '',
            
            // Jalur & Gelombang
            $pendaftar->jalurPendaftaran?->nama ?? '',
            $pendaftar->gelombangPendaftaran?->nama ?? '',
            $pendaftar->pilihan_program ?? '',
            
            // Status
            $this->formatStatus($pendaftar->status_verifikasi),
            $this->formatStatusAdmisi($pendaftar->status_admisi),
            $pendaftar->is_finalisasi ? 'Ya' : 'Tidak',
            $pendaftar->tanggal_registrasi ? $pendaftar->tanggal_registrasi->format('d/m/Y H:i') : '',
            
            // Data Orang Tua - Ayah
            $ortu?->no_kk ?? '',
            $this->formatStatusOrtu($ortu?->status_ayah),
            $ortu?->nik_ayah ?? '',
            $ortu?->nama_ayah ?? '',
            $ortu?->tempat_lahir_ayah ?? '',
            $ortu?->tanggal_lahir_ayah ? $ortu->tanggal_lahir_ayah->format('d/m/Y') : '',
            $this->formatPendidikan($ortu?->pendidikan_ayah),
            $this->formatPekerjaan($ortu?->pekerjaan_ayah),
            $this->formatPenghasilan($ortu?->penghasilan_ayah),
            $ortu?->hp_ayah ?? '',
            
            // Data Orang Tua - Ibu
            $this->formatStatusOrtu($ortu?->status_ibu),
            $ortu?->nik_ibu ?? '',
            $ortu?->nama_ibu ?? '',
            $ortu?->tempat_lahir_ibu ?? '',
            $ortu?->tanggal_lahir_ibu ? $ortu->tanggal_lahir_ibu->format('d/m/Y') : '',
            $this->formatPendidikan($ortu?->pendidikan_ibu),
            $this->formatPekerjaan($ortu?->pekerjaan_ibu),
            $this->formatPenghasilan($ortu?->penghasilan_ibu),
            $ortu?->hp_ibu ?? '',
            
            // Data Wali
            $ortu?->tinggal_dengan_wali ? 'Ya' : 'Tidak',
            $ortu?->nama_wali ?? '',
            $ortu?->hubungan_wali ?? '',
            $ortu?->nik_wali ?? '',
            $this->formatPendidikan($ortu?->pendidikan_wali),
            $this->formatPekerjaan($ortu?->pekerjaan_wali),
            $this->formatPenghasilan($ortu?->penghasilan_wali),
            $ortu?->no_hp_wali ?? '',
            
            // Alamat Orang Tua
            $ortu?->alamat_ortu ?? '',
            
            // Nilai Rapor Semester 1-5
            $nilai[1]['mtk'],
            $nilai[1]['ipa'],
            $nilai[1]['ips'],
            $nilai[1]['rata'],
            $nilai[2]['mtk'],
            $nilai[2]['ipa'],
            $nilai[2]['ips'],
            $nilai[2]['rata'],
            $nilai[3]['mtk'],
            $nilai[3]['ipa'],
            $nilai[3]['ips'],
            $nilai[3]['rata'],
            $nilai[4]['mtk'],
            $nilai[4]['ipa'],
            $nilai[4]['ips'],
            $nilai[4]['rata'],
            $nilai[5]['mtk'],
            $nilai[5]['ipa'],
            $nilai[5]['ips'],
            $nilai[5]['rata'],
            
            // Nilai Akhir
            $pendaftar->nilai_cbt ?? '',
            $pendaftar->nilai_wawancara ?? '',
            $pendaftar->nilai_akhir ?? '',
            $pendaftar->ranking ?? '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $lastColumn = $sheet->getHighestColumn();

        return [
            // Header row styling
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ],
            // All cells border
            "A1:{$lastColumn}{$lastRow}" => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 15,  // Nomor Tes
            'C' => 18,  // No Registrasi
            'D' => 15,  // NISN
            'E' => 18,  // NIK
            'F' => 30,  // Nama Lengkap
            'G' => 12,  // Jenis Kelamin
            'H' => 15,  // Tempat Lahir
            'I' => 12,  // Tanggal Lahir
            'J' => 12,  // Agama
        ];
    }

    public function title(): string
    {
        return $this->type === 'with_nomor_tes' ? 'Peserta Ujian' : 'Semua Pendaftar';
    }

    // Helper methods
    private function formatStatus($status): string
    {
        return match($status) {
            'pending' => 'Menunggu',
            'verified' => 'Terverifikasi',
            'rejected' => 'Ditolak',
            'revisi' => 'Perlu Revisi',
            default => $status ?? '',
        };
    }

    private function formatStatusAdmisi($status): string
    {
        return match($status) {
            'diterima' => 'Diterima',
            'ditolak' => 'Ditolak',
            'cadangan' => 'Cadangan',
            default => $status ?? '-',
        };
    }

    private function formatStatusOrtu($status): string
    {
        return match($status) {
            'masih_hidup' => 'Masih Hidup',
            'meninggal' => 'Meninggal',
            default => $status ?? '',
        };
    }

    private function formatPendidikan($pendidikan): string
    {
        $list = [
            'tidak_sekolah' => 'Tidak Sekolah',
            'sd' => 'SD/Sederajat',
            'smp' => 'SMP/Sederajat',
            'sma' => 'SMA/Sederajat',
            'd1' => 'D1',
            'd2' => 'D2',
            'd3' => 'D3',
            'd4' => 'D4/S1',
            's1' => 'S1',
            's2' => 'S2',
            's3' => 'S3',
        ];
        return $list[$pendidikan] ?? $pendidikan ?? '';
    }

    private function formatPekerjaan($pekerjaan): string
    {
        $list = [
            'tidak_bekerja' => 'Tidak Bekerja',
            'pensiunan' => 'Pensiunan',
            'pns' => 'PNS',
            'tni_polri' => 'TNI/Polisi',
            'guru_dosen' => 'Guru/Dosen',
            'pegawai_swasta' => 'Pegawai Swasta',
            'wiraswasta' => 'Wiraswasta',
            'buruh' => 'Buruh',
            'petani' => 'Petani',
            'nelayan' => 'Nelayan',
            'sudah_meninggal' => 'Sudah Meninggal',
            'lainnya' => 'Lainnya',
        ];
        return $list[$pekerjaan] ?? $pekerjaan ?? '';
    }

    private function formatPenghasilan($penghasilan): string
    {
        $list = [
            'dibawah_800rb' => 'Dibawah Rp 800.000',
            '800rb_1_2jt' => 'Rp 800.001 - Rp 1.200.000',
            '1_2jt_1_8jt' => 'Rp 1.200.001 - Rp 1.800.000',
            '1_8jt_2_5jt' => 'Rp 1.800.001 - Rp 2.500.000',
            '2_5jt_3_5jt' => 'Rp 2.500.001 - Rp 3.500.000',
            '3_5jt_4_8jt' => 'Rp 3.500.001 - Rp 4.800.000',
            '4_8jt_6_5jt' => 'Rp 4.800.001 - Rp 6.500.000',
            '6_5jt_10jt' => 'Rp 6.500.001 - Rp 10.000.000',
            '10jt_20jt' => 'Rp 10.000.001 - Rp 20.000.000',
            'diatas_20jt' => 'Diatas Rp 20.000.000',
        ];
        return $list[$penghasilan] ?? $penghasilan ?? '';
    }
}
