<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KelulusanSetting extends Model
{
    use HasUuids;

    protected $table = 'kelulusan_settings';

    protected $fillable = [
        'tahun_pelajaran_id',
        'judul_pengumuman',
        'pesan_lulus',
        'pesan_tidak_lulus',
        'link_grup_wa',
        'nama_grup_wa',
        'dokumen_persyaratan',
        'template_surat_pernyataan',
        'file_konsider',
        'tampilkan_pengumuman',
        'tanggal_pengumuman',
        'tampilkan_link_wa',
        'tampilkan_dokumen',
        'tanggal_daftar_ulang_mulai',
        'tanggal_daftar_ulang_selesai',
        'catatan_daftar_ulang',
    ];

    protected $casts = [
        'dokumen_persyaratan' => 'array',
        'tampilkan_pengumuman' => 'boolean',
        'tampilkan_link_wa' => 'boolean',
        'tampilkan_dokumen' => 'boolean',
        'tanggal_pengumuman' => 'datetime',
        'tanggal_daftar_ulang_mulai' => 'date',
        'tanggal_daftar_ulang_selesai' => 'date',
    ];

    /**
     * Cek apakah pengumuman sudah aktif berdasarkan toggle + tanggal/jam
     * - tampilkan_pengumuman harus true
     * - Jika tanggal_pengumuman diisi, waktu sekarang harus >= tanggal_pengumuman
     * - Jika tanggal_pengumuman kosong, langsung aktif saat toggle ON
     */
    public function isPengumumanAktif(): bool
    {
        if (!$this->tampilkan_pengumuman) {
            return false;
        }

        if ($this->tanggal_pengumuman && now()->lt($this->tanggal_pengumuman)) {
            return false;
        }

        return true;
    }

    // Relations
    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    /**
     * Get setting for active tahun pelajaran
     */
    public static function getActive()
    {
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        if (!$tahunAktif) return null;

        return static::firstOrCreate(
            ['tahun_pelajaran_id' => $tahunAktif->id],
            [
                'judul_pengumuman' => 'Pengumuman Kelulusan PPDB',
                'pesan_lulus' => 'Selamat! Anda dinyatakan LULUS seleksi PPDB. Silakan bergabung ke grup WhatsApp dan lengkapi persyaratan daftar ulang.',
                'pesan_tidak_lulus' => 'Mohon maaf, Anda belum dinyatakan lulus pada seleksi PPDB tahun ini. Tetap semangat dan jangan menyerah!',
            ]
        );
    }
}
