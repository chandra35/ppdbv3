<?php

namespace App\Http\Controllers\Api\Internal;

use App\Http\Controllers\Controller;
use App\Models\CalonDokumen;
use App\Models\CalonSiswa;
use App\Models\Registrasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class SimansaSyncController extends Controller
{
    public function pendaftar(Request $request)
    {
        $this->authorizeToken($request);

        $validated = $request->validate([
            'q' => 'nullable|string|max:100',
            'ids' => 'nullable|string|max:4000',
            'tahun_nama' => 'nullable|string|max:30',
            'tahun_mulai' => 'nullable|integer|min:2000|max:2100',
            'tahun_selesai' => 'nullable|integer|min:2000|max:2100',
            'limit' => 'nullable|integer|min:1|max:100',
            'per_page' => 'nullable|integer|min:1|max:200',
            'page' => 'nullable|integer|min:1|max:1000',
            'include_documents' => 'nullable|boolean',
            'scope' => 'nullable|in:eligible,all',
        ]);

        $scope = $validated['scope'] ?? 'eligible';
        $ids = collect(explode(',', (string) ($validated['ids'] ?? '')))
            ->map(fn ($id) => trim($id))
            ->filter()
            ->values();

        $query = CalonSiswa::query()
            ->with(['tahunPelajaran', 'jalurPendaftaran', 'gelombangPendaftaran', 'kelulusan'])
            ->whereNull('deleted_at');

        if ($scope === 'eligible') {
            $query->whereHas('kelulusan', fn ($q) => $q->where('status', 'lulus'))
                ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('registrasis')
                    ->whereColumn('registrasis.calon_siswa_id', 'calon_siswas.id')
                    ->whereNull('registrasis.deleted_at');
            });
        }

        if ($ids->isNotEmpty()) {
            $query->whereIn('id', $ids);
        } elseif (!empty($validated['q'])) {
            $like = '%' . str_replace(' ', '%', trim($validated['q'])) . '%';
            $query->where(function ($q) use ($like) {
                $q->where('nama_lengkap', 'like', $like)
                    ->orWhere('nisn', 'like', $like)
                    ->orWhere('nomor_registrasi', 'like', $like)
                    ->orWhere('nomor_tes', 'like', $like);
            });
        }

        $this->applyTahunFilter($query, $validated);

        $limit = (int) ($validated['limit'] ?? 0);
        $perPage = (int) ($validated['per_page'] ?? ($limit ?: 20));
        $perPage = max(1, min($perPage, 200));
        $page = (int) ($validated['page'] ?? 1);

        if ($ids->isNotEmpty()) {
            $rows = $query->orderBy('nama_lengkap')->limit($perPage)->get();

            return response()->json([
                'data' => $rows->map(fn ($calon) => $this->formatCalon($calon, (bool) ($validated['include_documents'] ?? false)))->values(),
                'meta' => [
                    'current_page' => 1,
                    'per_page' => $perPage,
                    'total' => $rows->count(),
                    'last_page' => 1,
                ],
            ]);
        }

        $paginator = $query
            ->orderBy('nama_lengkap')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $paginator->getCollection()
                ->map(fn ($calon) => $this->formatCalon($calon, (bool) ($validated['include_documents'] ?? false)))
                ->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function dokumen(Request $request, CalonDokumen $dokumen)
    {
        $this->authorizeToken($request);

        if ($dokumen->storage_disk === 'gdrive' && $dokumen->remote_file_id) {
            return redirect()->away('https://drive.google.com/uc?export=download&id=' . $dokumen->remote_file_id);
        }

        if (!empty($dokumen->remote_file_url)) {
            return redirect()->away($dokumen->remote_file_url);
        }

        $disk = $dokumen->storage_disk ?: 'public';
        if ($disk !== 'public' || empty($dokumen->file_path) || !Storage::disk('public')->exists($dokumen->file_path)) {
            abort(Response::HTTP_NOT_FOUND, 'Dokumen tidak ditemukan.');
        }

        return response()->file(
            Storage::disk('public')->path($dokumen->file_path),
            [
                'Content-Type' => $dokumen->mime_type ?: 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="' . addslashes($dokumen->nama_file ?: basename($dokumen->file_path)) . '"',
            ]
        );
    }

    private function authorizeToken(Request $request): void
    {
        $token = (string) config('services.simansa_sync.token');
        if ($token === '' || !hash_equals($token, (string) $request->bearerToken())) {
            abort(Response::HTTP_UNAUTHORIZED, 'Token sinkronisasi tidak valid.');
        }
    }

    private function applyTahunFilter($query, array $validated): void
    {
        if (empty($validated['tahun_nama']) && empty($validated['tahun_mulai'])) {
            return;
        }

        $normalizedName = preg_replace('/[^0-9]/', '', (string) ($validated['tahun_nama'] ?? ''));
        $query->whereHas('tahunPelajaran', function ($q) use ($normalizedName) {
            $q->where(function ($yearQ) use ($normalizedName) {
                if ($normalizedName !== '') {
                    $yearQ->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(nama, ' ', ''), '/', ''), '-', ''), '.', '') = ?", [$normalizedName]);
                }
            });
        });
    }

    private function formatCalon(CalonSiswa $calon, bool $includeDocuments): array
    {
        $registrasi = Registrasi::query()
            ->where('calon_siswa_id', $calon->id)
            ->whereNull('deleted_at')
            ->latest('tanggal_registrasi')
            ->first();
        $kelulusan = $calon->kelulusan;

        $documents = $includeDocuments
            ? $calon->dokumen()->whereNull('deleted_at')->orderBy('jenis_dokumen')->get()
            : collect();

        return [
            'id' => $calon->id,
            'nomor_registrasi' => $calon->nomor_registrasi,
            'nomor_tes' => $calon->nomor_tes,
            'nisn' => $calon->nisn,
            'nik' => $calon->nik,
            'nama_lengkap' => $calon->nama_lengkap,
            'jenis_kelamin' => $calon->jenis_kelamin,
            'tempat_lahir' => $calon->tempat_lahir,
            'tanggal_lahir' => optional($calon->tanggal_lahir)->toDateString(),
            'agama' => $calon->agama,
            'jumlah_saudara' => $calon->jumlah_saudara,
            'anak_ke' => $calon->anak_ke,
            'hobi' => $calon->hobi,
            'cita_cita' => $calon->cita_cita,
            'nomor_hp' => $calon->nomor_hp,
            'email' => $calon->email,
            'npsn_asal_sekolah' => $calon->npsn_asal_sekolah,
            'nsm_asal_sekolah' => $calon->nsm_asal_sekolah,
            'nama_sekolah_asal' => $calon->nama_sekolah_asal,
            'alamat_sekolah_asal' => $calon->alamat_sekolah_asal,
            'alamat_sama_ortu' => $calon->alamat_sama_ortu,
            'jenis_tempat_tinggal' => $calon->jenis_tempat_tinggal,
            'alamat_siswa' => $calon->alamat_siswa,
            'rt_siswa' => $calon->rt_siswa,
            'rw_siswa' => $calon->rw_siswa,
            'provinsi_id_siswa' => $calon->provinsi_id_siswa,
            'kabupaten_id_siswa' => $calon->kabupaten_id_siswa,
            'kecamatan_id_siswa' => $calon->kecamatan_id_siswa,
            'kelurahan_id_siswa' => $calon->kelurahan_id_siswa,
            'kodepos_siswa' => $calon->kodepos_siswa,
            'data_diri_completed' => $calon->data_diri_completed,
            'data_ortu_completed' => $calon->data_ortu_completed,
            'data_dokumen_completed' => $calon->data_dokumen_completed,
            'pilihan_program' => $calon->pilihan_program,
            'is_finalisasi' => $calon->is_finalisasi,
            'tanggal_finalisasi' => optional($calon->tanggal_finalisasi)->toDateTimeString(),
            'tahun_pelajaran_id' => $calon->tahun_pelajaran_id,
            'ppdb_tahun_nama' => $calon->tahunPelajaran?->nama,
            'ppdb_tahun_mulai' => $calon->tahunPelajaran?->tahun_mulai,
            'ppdb_tahun_selesai' => $calon->tahunPelajaran?->tahun_selesai,
            'jalur_nama' => $calon->jalurPendaftaran?->nama,
            'gelombang_nama' => $calon->gelombangPendaftaran?->nama,
            'jurusan_awal' => $registrasi?->jurusan_awal,
            'jurusan_final' => $registrasi?->jurusan_final,
            'pindah_jurusan' => (bool) ($registrasi?->pindah_jurusan ?? false),
            'status_kelulusan' => $kelulusan?->status,
            'is_lulus' => $kelulusan?->status === 'lulus',
            'has_registrasi_komite' => (bool) $registrasi,
            'tanggal_registrasi_komite' => optional($registrasi?->tanggal_registrasi)->toDateTimeString(),
            'tanggal_kelulusan' => optional($kelulusan?->tanggal_kelulusan)->toDateTimeString(),
            'ortu' => $calon->ortu ? $calon->ortu->toArray() : null,
            'documents_count' => $calon->dokumen()->whereNull('deleted_at')->count(),
            'documents' => $documents->map(fn ($dokumen) => $this->formatDokumen($dokumen))->values(),
        ];
    }

    private function formatDokumen(CalonDokumen $dokumen): array
    {
        return [
            'id' => $dokumen->id,
            'jenis_dokumen' => $dokumen->jenis_dokumen,
            'nama_dokumen' => $dokumen->nama_dokumen,
            'nama_file' => $dokumen->nama_file,
            'file_path' => $dokumen->file_path,
            'file_size' => $dokumen->file_size,
            'mime_type' => $dokumen->mime_type,
            'storage_disk' => $dokumen->storage_disk,
            'remote_file_id' => $dokumen->remote_file_id,
            'remote_file_url' => $dokumen->remote_file_url,
            'status_verifikasi' => $dokumen->status_verifikasi,
            'download_url' => route('api.internal.simansa.dokumen', $dokumen),
        ];
    }
}
