@extends('layouts.app')

@section('title', 'Step 2: Data Pribadi')

@section('content')
<div style="max-width: 600px; margin: 2rem auto;">
    <div class="card">
        <h2 style="color: #667eea; margin-bottom: 2rem;">Langkah 2: Data Pribadi & Orang Tua</h2>
        
        <div style="background: #f0f4ff; padding: 1rem; border-radius: 4px; margin-bottom: 2rem; border-left: 4px solid #667eea;">
            <p style="margin: 0; color: #333;">Lengkapi data pribadi Anda dan data orang tua/wali. Pastikan semua informasi akurat.</p>
        </div>

        <form method="POST" action="{{ route('ppdb.register.step2.store') }}">
            @csrf
            
            <h4 style="color: #667eea; margin: 2rem 0 1rem 0;">Data Pribadi</h4>
            
            <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap</label>
                <input type="text" id="nama_lengkap" name="nama_lengkap" required value="{{ old('nama_lengkap') }}">
            </div>

            <div class="form-group">
                <label for="tempat_lahir">Tempat Lahir</label>
                <input type="text" id="tempat_lahir" name="tempat_lahir" required value="{{ old('tempat_lahir') }}">
            </div>

            <div class="form-group">
                <label for="tanggal_lahir">Tanggal Lahir</label>
                <input type="date" id="tanggal_lahir" name="tanggal_lahir" required value="{{ old('tanggal_lahir') }}">
            </div>

            <div class="form-group">
                <label for="jenis_kelamin">Jenis Kelamin</label>
                <select id="jenis_kelamin" name="jenis_kelamin" required>
                    <option value="">-- Pilih --</option>
                    <option value="laki-laki" {{ old('jenis_kelamin') == 'laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="perempuan" {{ old('jenis_kelamin') == 'perempuan' ? 'selected' : '' }}>Perempuan</option>
                </select>
            </div>

            <div class="form-group">
                <label for="agama">Agama</label>
                <select id="agama" name="agama">
                    <option value="">-- Pilih --</option>
                    <option value="islam" {{ old('agama') == 'islam' ? 'selected' : '' }}>Islam</option>
                    <option value="kristen" {{ old('agama') == 'kristen' ? 'selected' : '' }}>Kristen</option>
                    <option value="katolik" {{ old('agama') == 'katolik' ? 'selected' : '' }}>Katolik</option>
                    <option value="hindu" {{ old('agama') == 'hindu' ? 'selected' : '' }}>Hindu</option>
                    <option value="budha" {{ old('agama') == 'budha' ? 'selected' : '' }}>Budha</option>
                    <option value="konghucu" {{ old('agama') == 'konghucu' ? 'selected' : '' }}>Konghucu</option>
                </select>
            </div>

            <div class="form-group">
                <label for="no_hp_pribadi">No. HP Pribadi</label>
                <input type="tel" id="no_hp_pribadi" name="no_hp_pribadi" placeholder="08xxxxxxxxxx" value="{{ old('no_hp_pribadi') }}">
            </div>

            <h4 style="color: #667eea; margin: 2rem 0 1rem 0;">Alamat Rumah</h4>

            <div class="form-group">
                <label for="alamat_rumah">Alamat Lengkap</label>
                <textarea id="alamat_rumah" name="alamat_rumah" rows="3" required>{{ old('alamat_rumah') }}</textarea>
            </div>

            <div class="form-group">
                <label for="kelurahan">Kelurahan/Desa</label>
                <input type="text" id="kelurahan" name="kelurahan" required value="{{ old('kelurahan') }}">
            </div>

            <div class="form-group">
                <label for="kecamatan">Kecamatan</label>
                <input type="text" id="kecamatan" name="kecamatan" required value="{{ old('kecamatan') }}">
            </div>

            <div class="form-group">
                <label for="kabupaten_kota">Kabupaten/Kota</label>
                <input type="text" id="kabupaten_kota" name="kabupaten_kota" required value="{{ old('kabupaten_kota') }}">
            </div>

            <div class="form-group">
                <label for="provinsi">Provinsi</label>
                <input type="text" id="provinsi" name="provinsi" required value="{{ old('provinsi') }}">
            </div>

            <h4 style="color: #667eea; margin: 2rem 0 1rem 0;">Data Orang Tua/Wali</h4>

            <div class="form-group">
                <label for="no_hp_ortu">No. HP Orang Tua/Wali</label>
                <input type="tel" id="no_hp_ortu" name="no_hp_ortu" placeholder="08xxxxxxxxxx" value="{{ old('no_hp_ortu') }}">
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <a href="{{ route('ppdb.register.step1') }}" class="btn btn-secondary" style="flex: 1; text-align: center;">Kembali</a>
                <button type="submit" class="btn btn-primary" style="flex: 1;">Lanjut ke Step 3</button>
            </div>
        </form>
    </div>
</div>
@endsection
