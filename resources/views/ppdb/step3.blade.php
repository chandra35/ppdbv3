@extends('layouts.app')

@section('title', 'Step 3: Data Orang Tua')

@section('content')
<div style="max-width: 900px; margin: 2rem auto;">
    <div class="card">
        <h2 style="color: #667eea; margin-bottom: 1rem;">Langkah 3: Data Orang Tua/Wali</h2>
        
        {{-- Progress Bar --}}
        <div style="display: flex; gap: 0.5rem; margin-bottom: 2rem;">
            <div style="flex: 1; height: 6px; background: #28a745; border-radius: 3px;"></div>
            <div style="flex: 1; height: 6px; background: #28a745; border-radius: 3px;"></div>
            <div style="flex: 1; height: 6px; background: #667eea; border-radius: 3px;"></div>
            <div style="flex: 1; height: 6px; background: #e9ecef; border-radius: 3px;"></div>
            <div style="flex: 1; height: 6px; background: #e9ecef; border-radius: 3px;"></div>
        </div>
        
        <div style="background: #f0f4ff; padding: 1rem; border-radius: 4px; margin-bottom: 2rem; border-left: 4px solid #667eea;">
            <p style="margin: 0; color: #333;">Lengkapi data orang tua dan/atau wali. Jika tinggal dengan wali, centang opsi tersebut.</p>
        </div>

        <form method="POST" action="{{ route('ppdb.register.step3.store') }}" id="formStep3">
            @csrf
            
            {{-- No KK --}}
            <div class="form-group">
                <label for="no_kk">Nomor Kartu Keluarga (16 digit) <span style="color: red;">*</span></label>
                <input type="text" id="no_kk" name="no_kk" maxlength="16" pattern="[0-9]{16}" required value="{{ old('no_kk') }}" placeholder="16 digit nomor KK">
                @error('no_kk') <small style="color: red;">{{ $message }}</small> @enderror
            </div>

            {{-- Data Ayah --}}
            <h4 style="color: #667eea; margin: 2rem 0 1rem 0; border-bottom: 1px solid #e9ecef; padding-bottom: 0.5rem;">
                <i class="fas fa-male"></i> Data Ayah
            </h4>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="status_ayah">Status <span style="color: red;">*</span></label>
                    <select id="status_ayah" name="status_ayah" required>
                        <option value="masih_hidup" {{ old('status_ayah', 'masih_hidup') == 'masih_hidup' ? 'selected' : '' }}>Masih Hidup</option>
                        <option value="meninggal" {{ old('status_ayah') == 'meninggal' ? 'selected' : '' }}>Meninggal</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="nik_ayah">NIK Ayah</label>
                    <input type="text" id="nik_ayah" name="nik_ayah" maxlength="16" value="{{ old('nik_ayah') }}">
                </div>

                <div class="form-group">
                    <label for="nama_ayah">Nama Ayah <span style="color: red;">*</span></label>
                    <input type="text" id="nama_ayah" name="nama_ayah" required value="{{ old('nama_ayah', $emisData['nama_ayah'] ?? '') }}">
                    @error('nama_ayah') <small style="color: red;">{{ $message }}</small> @enderror
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="tempat_lahir_ayah">Tempat Lahir</label>
                    <input type="text" id="tempat_lahir_ayah" name="tempat_lahir_ayah" value="{{ old('tempat_lahir_ayah') }}">
                </div>

                <div class="form-group">
                    <label for="tanggal_lahir_ayah">Tanggal Lahir</label>
                    <input type="date" id="tanggal_lahir_ayah" name="tanggal_lahir_ayah" value="{{ old('tanggal_lahir_ayah') }}">
                </div>

                <div class="form-group">
                    <label for="pendidikan_ayah">Pendidikan</label>
                    <select id="pendidikan_ayah" name="pendidikan_ayah">
                        <option value="">-- Pilih --</option>
                        @foreach($pendidikanOptions as $key => $label)
                        <option value="{{ $key }}" {{ old('pendidikan_ayah') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="pekerjaan_ayah">Pekerjaan</label>
                    <select id="pekerjaan_ayah" name="pekerjaan_ayah">
                        <option value="">-- Pilih --</option>
                        @foreach($pekerjaanOptions as $key => $label)
                        <option value="{{ $key }}" {{ old('pekerjaan_ayah') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="penghasilan_ayah">Penghasilan</label>
                    <select id="penghasilan_ayah" name="penghasilan_ayah">
                        <option value="">-- Pilih --</option>
                        @foreach($penghasilanOptions as $key => $label)
                        <option value="{{ $key }}" {{ old('penghasilan_ayah') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="no_hp_ayah">No. HP Ayah</label>
                    <input type="tel" id="no_hp_ayah" name="no_hp_ayah" placeholder="08xxxxxxxxxx" value="{{ old('no_hp_ayah') }}">
                </div>
            </div>

            {{-- Data Ibu --}}
            <h4 style="color: #667eea; margin: 2rem 0 1rem 0; border-bottom: 1px solid #e9ecef; padding-bottom: 0.5rem;">
                <i class="fas fa-female"></i> Data Ibu
            </h4>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="status_ibu">Status <span style="color: red;">*</span></label>
                    <select id="status_ibu" name="status_ibu" required>
                        <option value="masih_hidup" {{ old('status_ibu', 'masih_hidup') == 'masih_hidup' ? 'selected' : '' }}>Masih Hidup</option>
                        <option value="meninggal" {{ old('status_ibu') == 'meninggal' ? 'selected' : '' }}>Meninggal</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="nik_ibu">NIK Ibu</label>
                    <input type="text" id="nik_ibu" name="nik_ibu" maxlength="16" value="{{ old('nik_ibu') }}">
                </div>

                <div class="form-group">
                    <label for="nama_ibu">Nama Ibu <span style="color: red;">*</span></label>
                    <input type="text" id="nama_ibu" name="nama_ibu" required value="{{ old('nama_ibu', $emisData['nama_ibu'] ?? '') }}">
                    @error('nama_ibu') <small style="color: red;">{{ $message }}</small> @enderror
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="tempat_lahir_ibu">Tempat Lahir</label>
                    <input type="text" id="tempat_lahir_ibu" name="tempat_lahir_ibu" value="{{ old('tempat_lahir_ibu') }}">
                </div>

                <div class="form-group">
                    <label for="tanggal_lahir_ibu">Tanggal Lahir</label>
                    <input type="date" id="tanggal_lahir_ibu" name="tanggal_lahir_ibu" value="{{ old('tanggal_lahir_ibu') }}">
                </div>

                <div class="form-group">
                    <label for="pendidikan_ibu">Pendidikan</label>
                    <select id="pendidikan_ibu" name="pendidikan_ibu">
                        <option value="">-- Pilih --</option>
                        @foreach($pendidikanOptions as $key => $label)
                        <option value="{{ $key }}" {{ old('pendidikan_ibu') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="pekerjaan_ibu">Pekerjaan</label>
                    <select id="pekerjaan_ibu" name="pekerjaan_ibu">
                        <option value="">-- Pilih --</option>
                        @foreach($pekerjaanOptions as $key => $label)
                        <option value="{{ $key }}" {{ old('pekerjaan_ibu') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="penghasilan_ibu">Penghasilan</label>
                    <select id="penghasilan_ibu" name="penghasilan_ibu">
                        <option value="">-- Pilih --</option>
                        @foreach($penghasilanOptions as $key => $label)
                        <option value="{{ $key }}" {{ old('penghasilan_ibu') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="no_hp_ibu">No. HP Ibu</label>
                    <input type="tel" id="no_hp_ibu" name="no_hp_ibu" placeholder="08xxxxxxxxxx" value="{{ old('no_hp_ibu') }}">
                </div>
            </div>

            {{-- Data Wali (Optional) --}}
            <h4 style="color: #667eea; margin: 2rem 0 1rem 0; border-bottom: 1px solid #e9ecef; padding-bottom: 0.5rem;">
                <i class="fas fa-user-friends"></i> Data Wali (Opsional)
            </h4>

            <div class="form-group">
                <label style="display: flex; align-items: center; cursor: pointer;">
                    <input type="checkbox" id="tinggal_dengan_wali" name="tinggal_dengan_wali" value="1" {{ old('tinggal_dengan_wali') ? 'checked' : '' }} style="margin-right: 0.5rem;">
                    Siswa tinggal dengan Wali (bukan orang tua kandung)
                </label>
            </div>

            <div id="wali_section" style="display: {{ old('tinggal_dengan_wali') ? 'block' : 'none' }};">
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="nama_wali">Nama Wali</label>
                        <input type="text" id="nama_wali" name="nama_wali" value="{{ old('nama_wali') }}">
                    </div>

                    <div class="form-group">
                        <label for="hubungan_wali">Hubungan dengan Siswa</label>
                        <select id="hubungan_wali" name="hubungan_wali">
                            <option value="">-- Pilih --</option>
                            @foreach($hubunganWaliOptions as $key => $label)
                            <option value="{{ $key }}" {{ old('hubungan_wali') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="nik_wali">NIK Wali</label>
                        <input type="text" id="nik_wali" name="nik_wali" maxlength="16" value="{{ old('nik_wali') }}">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="tempat_lahir_wali">Tempat Lahir</label>
                        <input type="text" id="tempat_lahir_wali" name="tempat_lahir_wali" value="{{ old('tempat_lahir_wali') }}">
                    </div>

                    <div class="form-group">
                        <label for="tanggal_lahir_wali">Tanggal Lahir</label>
                        <input type="date" id="tanggal_lahir_wali" name="tanggal_lahir_wali" value="{{ old('tanggal_lahir_wali') }}">
                    </div>

                    <div class="form-group">
                        <label for="pendidikan_wali">Pendidikan</label>
                        <select id="pendidikan_wali" name="pendidikan_wali">
                            <option value="">-- Pilih --</option>
                            @foreach($pendidikanOptions as $key => $label)
                            <option value="{{ $key }}" {{ old('pendidikan_wali') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="pekerjaan_wali">Pekerjaan</label>
                        <select id="pekerjaan_wali" name="pekerjaan_wali">
                            <option value="">-- Pilih --</option>
                            @foreach($pekerjaanOptions as $key => $label)
                            <option value="{{ $key }}" {{ old('pekerjaan_wali') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="penghasilan_wali">Penghasilan</label>
                        <select id="penghasilan_wali" name="penghasilan_wali">
                            <option value="">-- Pilih --</option>
                            @foreach($penghasilanOptions as $key => $label)
                            <option value="{{ $key }}" {{ old('penghasilan_wali') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="no_hp_wali">No. HP Wali</label>
                        <input type="tel" id="no_hp_wali" name="no_hp_wali" placeholder="08xxxxxxxxxx" value="{{ old('no_hp_wali') }}">
                    </div>
                </div>
            </div>

            {{-- Alamat Orang Tua --}}
            <h4 style="color: #667eea; margin: 2rem 0 1rem 0; border-bottom: 1px solid #e9ecef; padding-bottom: 0.5rem;">
                <i class="fas fa-home"></i> Alamat Orang Tua
            </h4>

            <div class="form-group">
                <label for="alamat_ortu">Alamat Lengkap <span style="color: red;">*</span></label>
                <textarea id="alamat_ortu" name="alamat_ortu" rows="2" required placeholder="Jalan, Gang, Nomor Rumah">{{ old('alamat_ortu') }}</textarea>
                @error('alamat_ortu') <small style="color: red;">{{ $message }}</small> @enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="rt_ortu">RT</label>
                    <input type="text" id="rt_ortu" name="rt_ortu" maxlength="5" value="{{ old('rt_ortu') }}" placeholder="001">
                </div>

                <div class="form-group">
                    <label for="rw_ortu">RW</label>
                    <input type="text" id="rw_ortu" name="rw_ortu" maxlength="5" value="{{ old('rw_ortu') }}" placeholder="001">
                </div>

                <div class="form-group">
                    <label for="kode_pos_ortu">Kode Pos</label>
                    <input type="text" id="kode_pos_ortu" name="kode_pos_ortu" maxlength="10" value="{{ old('kode_pos_ortu') }}" placeholder="12345">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="provinsi_id_ortu">Provinsi <span style="color: red;">*</span></label>
                    <select id="provinsi_id_ortu" name="provinsi_id_ortu" required>
                        <option value="">-- Pilih Provinsi --</option>
                        @foreach($provinces as $code => $name)
                        <option value="{{ $code }}" {{ old('provinsi_id_ortu') == $code ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('provinsi_id_ortu') <small style="color: red;">{{ $message }}</small> @enderror
                </div>

                <div class="form-group">
                    <label for="kabupaten_id_ortu">Kabupaten/Kota <span style="color: red;">*</span></label>
                    <select id="kabupaten_id_ortu" name="kabupaten_id_ortu" required disabled>
                        <option value="">-- Pilih Kabupaten --</option>
                    </select>
                    @error('kabupaten_id_ortu') <small style="color: red;">{{ $message }}</small> @enderror
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="kecamatan_id_ortu">Kecamatan <span style="color: red;">*</span></label>
                    <select id="kecamatan_id_ortu" name="kecamatan_id_ortu" required disabled>
                        <option value="">-- Pilih Kecamatan --</option>
                    </select>
                    @error('kecamatan_id_ortu') <small style="color: red;">{{ $message }}</small> @enderror
                </div>

                <div class="form-group">
                    <label for="kelurahan_id_ortu">Kelurahan/Desa <span style="color: red;">*</span></label>
                    <select id="kelurahan_id_ortu" name="kelurahan_id_ortu" required disabled>
                        <option value="">-- Pilih Kelurahan --</option>
                    </select>
                    @error('kelurahan_id_ortu') <small style="color: red;">{{ $message }}</small> @enderror
                </div>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <a href="{{ route('ppdb.register.step2') }}" class="btn btn-secondary" style="flex: 1; text-align: center;">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    Lanjut ke Step 4 <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle wali section
    const tinggalDenganWali = document.getElementById('tinggal_dengan_wali');
    const waliSection = document.getElementById('wali_section');
    
    tinggalDenganWali.addEventListener('change', function() {
        waliSection.style.display = this.checked ? 'block' : 'none';
    });

    // Cascading dropdown for ortu address
    const provinsiSelect = document.getElementById('provinsi_id_ortu');
    const kabupatenSelect = document.getElementById('kabupaten_id_ortu');
    const kecamatanSelect = document.getElementById('kecamatan_id_ortu');
    const kelurahanSelect = document.getElementById('kelurahan_id_ortu');

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
