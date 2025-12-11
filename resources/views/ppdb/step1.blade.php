@extends('layouts.app')

@section('title', 'Step 1: Validasi NISN')

@section('content')
<div style="max-width: 700px; margin: 2rem auto;">
    <div class="card">
        <h2 style="color: #667eea; margin-bottom: 2rem;">Langkah 1: Validasi NISN</h2>
        
        <div style="background: #f0f4ff; padding: 1rem; border-radius: 4px; margin-bottom: 2rem; border-left: 4px solid #667eea;">
            <p style="margin: 0; color: #333;">
                Masukkan Nomor Induk Siswa Nasional (NISN) Anda untuk memulai pendaftaran.
            </p>
        </div>

        {{-- Info Jalur yang Aktif --}}
        <div style="background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; padding: 1rem; margin-bottom: 2rem;">
            <div style="display: flex; align-items: center;">
                <div style="width: 50px; height: 50px; border-radius: 50%; background: white; display: flex; align-items: center; justify-content: center; margin-right: 1rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <i class="{{ $jalurAktif->icon ?? 'fas fa-graduation-cap' }}" style="color: #28a745; font-size: 1.5rem;"></i>
                </div>
                <div>
                    <span style="display: block; font-size: 0.8rem; color: #666;"><i class="fas fa-door-open"></i> Pendaftaran Dibuka:</span>
                    <strong style="color: #155724; font-size: 1.25rem;">{{ $jalurAktif->nama }}</strong>
                    @if($jalurAktif->tanggal_tutup)
                    <div style="font-size: 0.85rem; color: #666; margin-top: 0.25rem;">
                        <i class="fas fa-calendar"></i> 
                        {{ $jalurAktif->tanggal_buka?->format('d M Y') ?? 'Sekarang' }} 
                        s/d 
                        {{ $jalurAktif->tanggal_tutup->format('d M Y') }}
                    </div>
                    @endif
                </div>
            </div>
            
            @if($jalurAktif->deskripsi)
            <p style="font-size: 0.875rem; color: #666; margin: 1rem 0 0 0; padding-top: 0.75rem; border-top: 1px solid #c3e6cb;">
                {{ $jalurAktif->deskripsi }}
            </p>
            @endif
            
            @if($jalurAktif->kuota)
            <div style="display: flex; justify-content: space-between; margin-top: 1rem; padding-top: 0.75rem; border-top: 1px solid #c3e6cb;">
                <div>
                    <span style="font-size: 0.8rem; color: #666;">Kuota Tersedia:</span>
                    <strong style="color: #155724;">{{ $jalurAktif->sisaKuota() }} kursi</strong>
                </div>
                <div>
                    <span style="font-size: 0.8rem; color: #666;">Kuota Total:</span>
                    <strong style="color: #155724;">{{ $jalurAktif->kuota }} kursi</strong>
                </div>
            </div>
            @endif
        </div>

        @if($jalurAktif->persyaratan)
        <div style="background: #fff3cd; padding: 1rem; border-radius: 8px; margin-bottom: 2rem; border: 1px solid #ffeeba;">
            <strong style="color: #856404;"><i class="fas fa-list-check"></i> Persyaratan Pendaftaran:</strong>
            <div style="margin-top: 0.5rem; font-size: 0.9rem; color: #856404;">
                {!! nl2br(e($jalurAktif->persyaratan)) !!}
            </div>
        </div>
        @endif

        <form method="POST" action="{{ route('ppdb.register.step1.validate') }}">
            @csrf
            
            <div class="form-group">
                <label for="nisn">NISN (10 digit) <span style="color: red;">*</span></label>
                <input type="text" 
                       id="nisn" 
                       name="nisn" 
                       placeholder="Contoh: 1234567890" 
                       maxlength="10" 
                       required 
                       value="{{ old('nisn') }}"
                       pattern="[0-9]{10}">
                <small style="color: #666; display: block; margin-top: 0.25rem;">Masukkan 10 digit NISN Anda</small>
                @error('nisn')
                <small style="color: red;">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Email <span style="color: red;">*</span></label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       placeholder="contoh@email.com" 
                       required 
                       value="{{ old('email') }}">
                <small style="color: #666; display: block; margin-top: 0.25rem;">Email akan digunakan untuk login</small>
                @error('email')
                <small style="color: red;">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Password <span style="color: red;">*</span></label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       placeholder="Minimal 8 karakter" 
                       required 
                       minlength="8">
                <small style="color: #666; display: block; margin-top: 0.25rem;">Password untuk login ke sistem</small>
                @error('password')
                <small style="color: red;">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Konfirmasi Password <span style="color: red;">*</span></label>
                <input type="password" 
                       id="password_confirmation" 
                       name="password_confirmation" 
                       placeholder="Ulang password" 
                       required 
                       minlength="8">
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <a href="{{ route('ppdb.landing') }}" class="btn btn-secondary" style="flex: 1; text-align: center;">Kembali</a>
                <button type="submit" class="btn btn-primary" style="flex: 1;">Validasi & Lanjutkan</button>
            </div>
        </form>
    </div>

    <div style="background: #fff3cd; padding: 1rem; border-radius: 4px; margin-top: 1.5rem;">
        <p style="margin: 0; color: #856404;">
            <strong>Catatan:</strong> Jika NISN Anda tidak valid di sistem Kemendikbud, hubungi sekolah Anda untuk mengecek data NISN.
        </p>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
@endsection
