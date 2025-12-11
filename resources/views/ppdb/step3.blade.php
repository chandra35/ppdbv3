@extends('layouts.app')

@section('title', 'Step 3: Upload Dokumen')

@section('content')
<div style="max-width: 700px; margin: 2rem auto;">
    <div class="card">
        <h2 style="color: #667eea; margin-bottom: 2rem;">Langkah 3: Upload Dokumen Pendukung</h2>
        
        <div style="background: #f0f4ff; padding: 1rem; border-radius: 4px; margin-bottom: 2rem; border-left: 4px solid #667eea;">
            <p style="margin: 0; color: #333;">Upload dokumen-dokumen pendukung yang diperlukan. Format: PDF, JPG, PNG (max 5MB per file)</p>
        </div>

        <form method="POST" action="{{ route('ppdb.register.step3.store') }}" enctype="multipart/form-data">
            @csrf
            
            <div class="form-group">
                <label>Ijazah/Sertifikat Kelulusan</label>
                <input type="file" name="ijazah" accept=".pdf,.jpg,.jpeg,.png">
                <small style="color: #666; display: block; margin-top: 0.25rem;">PDF, JPG, atau PNG (max 5MB)</small>
            </div>

            <div class="form-group">
                <label>Akta Kelahiran</label>
                <input type="file" name="akta_kelahiran" accept=".pdf,.jpg,.jpeg,.png">
                <small style="color: #666; display: block; margin-top: 0.25rem;">PDF, JPG, atau PNG (max 5MB)</small>
            </div>

            <div class="form-group">
                <label>Kartu Keluarga (KK)</label>
                <input type="file" name="kartu_keluarga" accept=".pdf,.jpg,.jpeg,.png">
                <small style="color: #666; display: block; margin-top: 0.25rem;">PDF, JPG, atau PNG (max 5MB)</small>
            </div>

            <div class="form-group">
                <label>Foto Pribadi 4x6</label>
                <input type="file" name="foto_4x6" accept=".jpg,.jpeg,.png">
                <small style="color: #666; display: block; margin-top: 0.25rem;">JPG atau PNG (max 5MB) - Warna atau B&W, latar belakang polos</small>
            </div>

            <div class="form-group">
                <label>Piagam/Sertifikat Prestasi (Opsional)</label>
                <input type="file" name="piagam_prestasi" accept=".pdf,.jpg,.jpeg,.png">
                <small style="color: #666; display: block; margin-top: 0.25rem;">PDF, JPG, atau PNG (max 5MB)</small>
            </div>

            <div class="form-group">
                <label>Surat Keterangan Sehat (Opsional)</label>
                <input type="file" name="surat_sehat" accept=".pdf,.jpg,.jpeg,.png">
                <small style="color: #666; display: block; margin-top: 0.25rem;">PDF, JPG, atau PNG (max 5MB)</small>
            </div>

            <div style="background: #fff3cd; padding: 1rem; border-radius: 4px; margin: 2rem 0;">
                <p style="margin: 0; color: #856404;">
                    <strong>Catatan:</strong> Dokumen yang ditandai dengan * (bintang) bersifat wajib. Dokumen yang ditandai dengan "(Opsional)" dapat ditambahkan kemudian.
                </p>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <a href="{{ route('ppdb.register.step2') }}" class="btn btn-secondary" style="flex: 1; text-align: center;">Kembali</a>
                <button type="submit" class="btn btn-primary" style="flex: 1;">Lanjut ke Step 4</button>
            </div>
        </form>
    </div>
</div>
@endsection
