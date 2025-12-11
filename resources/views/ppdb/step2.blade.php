@extends('layouts.app')

@section('title', 'Step 2: Data Diri Siswa')

@section('content')
<div style="max-width: 800px; margin: 2rem auto;">
    <div class="card">
        <h2 style="color: #667eea; margin-bottom: 1rem;">Langkah 2: Data Diri Siswa</h2>
        
        {{-- Progress Bar --}}
        <div style="display: flex; gap: 0.5rem; margin-bottom: 2rem;">
            <div style="flex: 1; height: 6px; background: #28a745; border-radius: 3px;"></div>
            <div style="flex: 1; height: 6px; background: #667eea; border-radius: 3px;"></div>
            <div style="flex: 1; height: 6px; background: #e9ecef; border-radius: 3px;"></div>
            <div style="flex: 1; height: 6px; background: #e9ecef; border-radius: 3px;"></div>
            <div style="flex: 1; height: 6px; background: #e9ecef; border-radius: 3px;"></div>
        </div>
        
        <div style="background: #f0f4ff; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; border-left: 4px solid #667eea;">
            <p style="margin: 0; color: #333;">Lengkapi data diri calon siswa. NISN: <strong>{{ $nisn }}</strong></p>
        </div>
        
        @if($emisData)
        <div style="background: #d4edda; padding: 1rem; border-radius: 4px; margin-bottom: 2rem; border-left: 4px solid #28a745;">
            <p style="margin: 0; color: #155724;">
                <i class="fas fa-check-circle"></i> <strong>Data EMIS ditemukan!</strong> Beberapa field telah diisi otomatis dari database EMIS. Silahkan periksa dan lengkapi data lainnya.
            </p>
        </div>
        @else
        <div style="background: #fff3cd; padding: 1rem; border-radius: 4px; margin-bottom: 2rem; border-left: 4px solid #ffc107;">
            <p style="margin: 0; color: #856404;">
                <i class="fas fa-exclamation-triangle"></i> Data NISN tidak ditemukan di EMIS. Silahkan isi semua data secara manual.
            </p>
        </div>
        @endif

        <form method="POST" action="{{ route('ppdb.register.step2.store') }}" id="formStep2">
            @csrf
            
            {{-- Identitas --}}
            <h4 style="color: #667eea; margin: 1.5rem 0 1rem 0; border-bottom: 1px solid #e9ecef; padding-bottom: 0.5rem;">
                <i class="fas fa-user"></i> Identitas
            </h4>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="nik">NIK (16 digit) <span style="color: red;">*</span></label>
                    <input type="text" id="nik" name="nik" maxlength="16" pattern="[0-9]{16}" required value="{{ old('nik') }}" placeholder="16 digit NIK">
                    @error('nik') <small style="color: red;">{{ $message }}</small> @enderror
                </div>

                <div class="form-group">
                    <label for="nama_lengkap">Nama Lengkap <span style="color: red;">*</span></label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" required value="{{ old('nama_lengkap', $emisData['nama'] ?? '') }}">
                    @error('nama_lengkap') <small style="color: red;">{{ $message }}</small> @enderror
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="tempat_lahir">Tempat Lahir <span style="color: red;">*</span></label>
                    <input type="text" id="tempat_lahir" name="tempat_lahir" required value="{{ old('tempat_lahir', $emisData['tempat_lahir'] ?? '') }}">
                    @error('tempat_lahir') <small style="color: red;">{{ $message }}</small> @enderror
                </div>

                <div class="form-group">
                    <label for="tanggal_lahir">Tanggal Lahir <span style="color: red;">*</span></label>
                    <input type="date" id="tanggal_lahir" name="tanggal_lahir" required value="{{ old('tanggal_lahir', $emisData['tanggal_lahir'] ?? '') }}">
                    @error('tanggal_lahir') <small style="color: red;">{{ $message }}</small> @enderror
                </div>

                <div class="form-group">
                    <label for="jenis_kelamin">Jenis Kelamin <span style="color: red;">*</span></label>
                    <select id="jenis_kelamin" name="jenis_kelamin" required>
                        <option value="">-- Pilih --</option>
                        <option value="L" {{ old('jenis_kelamin', $emisData['jenis_kelamin'] ?? '') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('jenis_kelamin', $emisData['jenis_kelamin'] ?? '') == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('jenis_kelamin') <small style="color: red;">{{ $message }}</small> @enderror
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="agama">Agama <span style="color: red;">*</span></label>
                    <select id="agama" name="agama" required>
                        <option value="">-- Pilih --</option>
                        @foreach($agamaOptions as $key => $label)
                        <option value="{{ $key }}" {{ old('agama') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('agama') <small style="color: red;">{{ $message }}</small> @enderror
                </div>

                <div class="form-group">
                    <label for="jumlah_saudara">Jumlah Saudara</label>
                    <input type="number" id="jumlah_saudara" name="jumlah_saudara" min="0" max="20" value="{{ old('jumlah_saudara') }}">
                </div>

                <div class="form-group">
                    <label for="anak_ke">Anak Ke</label>
                    <input type="number" id="anak_ke" name="anak_ke" min="1" max="20" value="{{ old('anak_ke') }}">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="hobi">Hobi</label>
                    <input type="text" id="hobi" name="hobi" value="{{ old('hobi') }}" placeholder="Contoh: Membaca, Olahraga">
                </div>

                <div class="form-group">
                    <label for="cita_cita">Cita-cita</label>
                    <input type="text" id="cita_cita" name="cita_cita" value="{{ old('cita_cita') }}" placeholder="Contoh: Dokter, Guru">
                </div>
            </div>

            {{-- Alamat Siswa --}}
            <h4 style="color: #667eea; margin: 2rem 0 1rem 0; border-bottom: 1px solid #e9ecef; padding-bottom: 0.5rem;">
                <i class="fas fa-map-marker-alt"></i> Alamat Siswa
            </h4>

            <div class="form-group">
                <label for="alamat_siswa">Alamat Lengkap <span style="color: red;">*</span></label>
                <textarea id="alamat_siswa" name="alamat_siswa" rows="2" required placeholder="Jalan, Gang, Nomor Rumah">{{ old('alamat_siswa') }}</textarea>
                @error('alamat_siswa') <small style="color: red;">{{ $message }}</small> @enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="rt_siswa">RT</label>
                    <input type="text" id="rt_siswa" name="rt_siswa" maxlength="5" value="{{ old('rt_siswa') }}" placeholder="001">
                </div>

                <div class="form-group">
                    <label for="rw_siswa">RW</label>
                    <input type="text" id="rw_siswa" name="rw_siswa" maxlength="5" value="{{ old('rw_siswa') }}" placeholder="001">
                </div>

                <div class="form-group">
                    <label for="kode_pos_siswa">Kode Pos</label>
                    <input type="text" id="kode_pos_siswa" name="kode_pos_siswa" maxlength="10" value="{{ old('kode_pos_siswa') }}" placeholder="12345">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="provinsi_id_siswa">Provinsi <span style="color: red;">*</span></label>
                    <select id="provinsi_id_siswa" name="provinsi_id_siswa" required>
                        <option value="">-- Pilih Provinsi --</option>
                        @foreach($provinces as $code => $name)
                        <option value="{{ $code }}" {{ old('provinsi_id_siswa') == $code ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('provinsi_id_siswa') <small style="color: red;">{{ $message }}</small> @enderror
                </div>

                <div class="form-group">
                    <label for="kabupaten_id_siswa">Kabupaten/Kota <span style="color: red;">*</span></label>
                    <select id="kabupaten_id_siswa" name="kabupaten_id_siswa" required disabled>
                        <option value="">-- Pilih Kabupaten --</option>
                    </select>
                    @error('kabupaten_id_siswa') <small style="color: red;">{{ $message }}</small> @enderror
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="kecamatan_id_siswa">Kecamatan <span style="color: red;">*</span></label>
                    <select id="kecamatan_id_siswa" name="kecamatan_id_siswa" required disabled>
                        <option value="">-- Pilih Kecamatan --</option>
                    </select>
                    @error('kecamatan_id_siswa') <small style="color: red;">{{ $message }}</small> @enderror
                </div>

                <div class="form-group">
                    <label for="kelurahan_id_siswa">Kelurahan/Desa <span style="color: red;">*</span></label>
                    <select id="kelurahan_id_siswa" name="kelurahan_id_siswa" required disabled>
                        <option value="">-- Pilih Kelurahan --</option>
                    </select>
                    @error('kelurahan_id_siswa') <small style="color: red;">{{ $message }}</small> @enderror
                </div>
            </div>

            {{-- Kontak & Sekolah Asal --}}
            <h4 style="color: #667eea; margin: 2rem 0 1rem 0; border-bottom: 1px solid #e9ecef; padding-bottom: 0.5rem;">
                <i class="fas fa-school"></i> Kontak & Sekolah Asal
            </h4>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="no_hp">No. HP</label>
                    <input type="tel" id="no_hp" name="no_hp" placeholder="08xxxxxxxxxx" value="{{ old('no_hp') }}">
                </div>

                <div class="form-group">
                    <label for="npsn_asal">NPSN Sekolah Asal</label>
                    <input type="text" id="npsn_asal" name="npsn_asal" maxlength="20" value="{{ old('npsn_asal') }}" placeholder="8 digit NPSN">
                </div>
            </div>

            <div class="form-group">
                <label for="sekolah_asal">Nama Sekolah Asal <span style="color: red;">*</span></label>
                <input type="text" id="sekolah_asal" name="sekolah_asal" required value="{{ old('sekolah_asal', $emisData['asal_sekolah'] ?? '') }}" placeholder="Contoh: SMP Negeri 1 Jakarta">
                @error('sekolah_asal') <small style="color: red;">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label for="alamat_sekolah_asal">Alamat Sekolah Asal</label>
                <input type="text" id="alamat_sekolah_asal" name="alamat_sekolah_asal" value="{{ old('alamat_sekolah_asal') }}">
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <a href="{{ route('ppdb.register.step1') }}" class="btn btn-secondary" style="flex: 1; text-align: center;">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    Lanjut ke Step 3 <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<script>
document.addEventListener('DOMContentLoaded', function() {
    const provinsiSelect = document.getElementById('provinsi_id_siswa');
    const kabupatenSelect = document.getElementById('kabupaten_id_siswa');
    const kecamatanSelect = document.getElementById('kecamatan_id_siswa');
    const kelurahanSelect = document.getElementById('kelurahan_id_siswa');

    provinsiSelect.addEventListener('change', function() {
        const provinceCode = this.value;
        kabupatenSelect.innerHTML = '<option value="">Loading...</option>';
        kabupatenSelect.disabled = true;
        kecamatanSelect.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
        kecamatanSelect.disabled = true;
        kelurahanSelect.innerHTML = '<option value="">-- Pilih Kelurahan --</option>';
        kelurahanSelect.disabled = true;

        if (provinceCode) {
            fetch('{{ route("ppdb.api.kabupaten") }}?province_code=' + provinceCode)
                .then(response => response.json())
                .then(data => {
                    kabupatenSelect.innerHTML = '<option value="">-- Pilih Kabupaten --</option>';
                    for (const [code, name] of Object.entries(data)) {
                        kabupatenSelect.innerHTML += `<option value="${code}">${name}</option>`;
                    }
                    kabupatenSelect.disabled = false;
                });
        }
    });

    kabupatenSelect.addEventListener('change', function() {
        const cityCode = this.value;
        kecamatanSelect.innerHTML = '<option value="">Loading...</option>';
        kecamatanSelect.disabled = true;
        kelurahanSelect.innerHTML = '<option value="">-- Pilih Kelurahan --</option>';
        kelurahanSelect.disabled = true;

        if (cityCode) {
            fetch('{{ route("ppdb.api.kecamatan") }}?city_code=' + cityCode)
                .then(response => response.json())
                .then(data => {
                    kecamatanSelect.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
                    for (const [code, name] of Object.entries(data)) {
                        kecamatanSelect.innerHTML += `<option value="${code}">${name}</option>`;
                    }
                    kecamatanSelect.disabled = false;
                });
        }
    });

    kecamatanSelect.addEventListener('change', function() {
        const districtCode = this.value;
        kelurahanSelect.innerHTML = '<option value="">Loading...</option>';
        kelurahanSelect.disabled = true;

        if (districtCode) {
            fetch('{{ route("ppdb.api.kelurahan") }}?district_code=' + districtCode)
                .then(response => response.json())
                .then(data => {
                    kelurahanSelect.innerHTML = '<option value="">-- Pilih Kelurahan --</option>';
                    for (const [code, name] of Object.entries(data)) {
                        kelurahanSelect.innerHTML += `<option value="${code}">${name}</option>`;
                    }
                    kelurahanSelect.disabled = false;
                });
        }
    });
});
</script>
@endsection
