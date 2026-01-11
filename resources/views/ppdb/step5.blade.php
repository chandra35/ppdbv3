@extends('layouts.app')

@section('title', 'Step 5: Review & Konfirmasi')

@section('content')
<div style="max-width: 900px; margin: 2rem auto;">
    <div class="card">
        <h2 style="color: #667eea; margin-bottom: 1rem;">Langkah 5: Review & Konfirmasi</h2>
        
        {{-- Progress Bar --}}
        <div style="display: flex; gap: 0.5rem; margin-bottom: 2rem;">
            <div style="flex: 1; height: 6px; background: #28a745; border-radius: 3px;"></div>
            <div style="flex: 1; height: 6px; background: #28a745; border-radius: 3px;"></div>
            <div style="flex: 1; height: 6px; background: #28a745; border-radius: 3px;"></div>
            <div style="flex: 1; height: 6px; background: #28a745; border-radius: 3px;"></div>
            <div style="flex: 1; height: 6px; background: #667eea; border-radius: 3px;"></div>
        </div>
        
        <div style="background: #d4edda; padding: 1rem; border-radius: 4px; margin-bottom: 2rem; border-left: 4px solid #28a745;">
            <p style="margin: 0; color: #155724;"><i class="fas fa-check-circle"></i> Semua data telah diisi. Silahkan review sebelum mengirim pendaftaran.</p>
        </div>

        {{-- Data Akun --}}
        <div style="border: 1px solid #e9ecef; border-radius: 8px; margin-bottom: 1.5rem; overflow: hidden;">
            <div style="background: #667eea; color: white; padding: 0.75rem 1rem; display: flex; justify-content: space-between; align-items: center;">
                <span><i class="fas fa-user-circle"></i> Data Akun</span>
                <a href="{{ route('ppdb.register.step1') }}" style="color: white; font-size: 0.85rem;"><i class="fas fa-edit"></i> Edit</a>
            </div>
            <div style="padding: 1rem;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                    <div><strong>NISN:</strong> {{ $nisn }}</div>
                    <div><strong>Email:</strong> {{ $email }}</div>
                </div>
            </div>
        </div>

        {{-- Data Diri Siswa --}}
        <div style="border: 1px solid #e9ecef; border-radius: 8px; margin-bottom: 1.5rem; overflow: hidden;">
            <div style="background: #667eea; color: white; padding: 0.75rem 1rem; display: flex; justify-content: space-between; align-items: center;">
                <span><i class="fas fa-id-card"></i> Data Diri Siswa</span>
                <a href="{{ route('ppdb.register.step2') }}" style="color: white; font-size: 0.85rem;"><i class="fas fa-edit"></i> Edit</a>
            </div>
            <div style="padding: 1rem;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <div><strong>NIK:</strong> {{ $dataSiswa['nik'] }}</div>
                    <div><strong>Nama Lengkap:</strong> {{ $dataSiswa['nama_lengkap'] }}</div>
                    <div><strong>Tempat/Tgl Lahir:</strong> {{ $dataSiswa['tempat_lahir'] }}, {{ \Carbon\Carbon::parse($dataSiswa['tanggal_lahir'])->format('d M Y') }}</div>
                    <div><strong>Jenis Kelamin:</strong> {{ $dataSiswa['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
                    <div><strong>Agama:</strong> {{ ucfirst($dataSiswa['agama']) }}</div>
                    <div><strong>Anak Ke:</strong> {{ $dataSiswa['anak_ke'] ?? '-' }} dari {{ $dataSiswa['jumlah_saudara'] ?? '-' }} bersaudara</div>
                    <div><strong>Hobi:</strong> {{ $dataSiswa['hobi'] ?? '-' }}</div>
                    <div><strong>Cita-cita:</strong> {{ $dataSiswa['cita_cita'] ?? '-' }}</div>
                </div>
                
                <hr style="margin: 1rem 0; border: none; border-top: 1px solid #e9ecef;">
                
                <div><strong>Alamat:</strong></div>
                <p style="margin: 0.25rem 0; color: #555;">
                    {{ $dataSiswa['alamat_siswa'] }}
                    {{ $dataSiswa['rt_siswa'] ? ', RT ' . $dataSiswa['rt_siswa'] : '' }}
                    {{ $dataSiswa['rw_siswa'] ? ', RW ' . $dataSiswa['rw_siswa'] : '' }}<br>
                    {{ $dataSiswa['kelurahan_nama'] }}, {{ $dataSiswa['kecamatan_nama'] }}<br>
                    {{ $dataSiswa['kabupaten_nama'] }}, {{ $dataSiswa['provinsi_nama'] }}
                    {{ $dataSiswa['kode_pos_siswa'] ? ' - ' . $dataSiswa['kode_pos_siswa'] : '' }}
                </p>
                
                <hr style="margin: 1rem 0; border: none; border-top: 1px solid #e9ecef;">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <div><strong>No. HP:</strong> {{ $dataSiswa['no_hp'] ?? '-' }}</div>
                    <div><strong>Sekolah Asal:</strong> {{ $dataSiswa['sekolah_asal'] }}</div>
                </div>
            </div>
        </div>

        {{-- Data Orang Tua --}}
        <div style="border: 1px solid #e9ecef; border-radius: 8px; margin-bottom: 1.5rem; overflow: hidden;">
            <div style="background: #667eea; color: white; padding: 0.75rem 1rem; display: flex; justify-content: space-between; align-items: center;">
                <span><i class="fas fa-users"></i> Data Orang Tua</span>
                <a href="{{ route('ppdb.register.step3') }}" style="color: white; font-size: 0.85rem;"><i class="fas fa-edit"></i> Edit</a>
            </div>
            <div style="padding: 1rem;">
                <div><strong>No. Kartu Keluarga:</strong> {{ $dataOrtu['no_kk'] }}</div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem;">
                    {{-- Ayah --}}
                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 4px;">
                        <h5 style="margin: 0 0 0.5rem 0; color: #667eea;"><i class="fas fa-male"></i> Ayah</h5>
                        <div style="font-size: 0.9rem;">
                            <div><strong>Nama:</strong> {{ $dataOrtu['nama_ayah'] }}</div>
                            <div><strong>Status:</strong> {{ $dataOrtu['status_ayah'] == 'masih_hidup' ? 'Masih Hidup' : 'Meninggal' }}</div>
                            @if($dataOrtu['nik_ayah'])
                            <div><strong>NIK:</strong> {{ $dataOrtu['nik_ayah'] }}</div>
                            @endif
                            @if($dataOrtu['pekerjaan_ayah'])
                            <div><strong>Pekerjaan:</strong> {{ ucfirst(str_replace('_', ' ', $dataOrtu['pekerjaan_ayah'])) }}</div>
                            @endif
                            @if($dataOrtu['no_hp_ayah'])
                            <div><strong>No. HP:</strong> {{ $dataOrtu['no_hp_ayah'] }}</div>
                            @endif
                        </div>
                    </div>
                    
                    {{-- Ibu --}}
                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 4px;">
                        <h5 style="margin: 0 0 0.5rem 0; color: #667eea;"><i class="fas fa-female"></i> Ibu</h5>
                        <div style="font-size: 0.9rem;">
                            <div><strong>Nama:</strong> {{ $dataOrtu['nama_ibu'] }}</div>
                            <div><strong>Status:</strong> {{ $dataOrtu['status_ibu'] == 'masih_hidup' ? 'Masih Hidup' : 'Meninggal' }}</div>
                            @if($dataOrtu['nik_ibu'])
                            <div><strong>NIK:</strong> {{ $dataOrtu['nik_ibu'] }}</div>
                            @endif
                            @if($dataOrtu['pekerjaan_ibu'])
                            <div><strong>Pekerjaan:</strong> {{ ucfirst(str_replace('_', ' ', $dataOrtu['pekerjaan_ibu'])) }}</div>
                            @endif
                            @if($dataOrtu['no_hp_ibu'])
                            <div><strong>No. HP:</strong> {{ $dataOrtu['no_hp_ibu'] }}</div>
                            @endif
                        </div>
                    </div>
                </div>
                
                @if(isset($dataOrtu['tinggal_dengan_wali']) && $dataOrtu['tinggal_dengan_wali'])
                <div style="background: #fff3cd; padding: 1rem; border-radius: 4px; margin-top: 1rem;">
                    <h5 style="margin: 0 0 0.5rem 0; color: #856404;"><i class="fas fa-user-friends"></i> Wali</h5>
                    <div style="font-size: 0.9rem;">
                        <div><strong>Nama:</strong> {{ $dataOrtu['nama_wali'] ?? '-' }}</div>
                        <div><strong>Hubungan:</strong> {{ ucfirst(str_replace('_', ' ', $dataOrtu['hubungan_wali'] ?? '-')) }}</div>
                    </div>
                </div>
                @endif
                
                <hr style="margin: 1rem 0; border: none; border-top: 1px solid #e9ecef;">
                
                <div><strong>Alamat Orang Tua:</strong></div>
                <p style="margin: 0.25rem 0; color: #555;">
                    {{ $dataOrtu['alamat_ortu'] }}
                    {{ $dataOrtu['rt_ortu'] ? ', RT ' . $dataOrtu['rt_ortu'] : '' }}
                    {{ $dataOrtu['rw_ortu'] ? ', RW ' . $dataOrtu['rw_ortu'] : '' }}<br>
                    {{ $dataOrtu['kelurahan_nama'] }}, {{ $dataOrtu['kecamatan_nama'] }}<br>
                    {{ $dataOrtu['kabupaten_nama'] }}, {{ $dataOrtu['provinsi_nama'] }}
                    {{ $dataOrtu['kode_pos_ortu'] ? ' - ' . $dataOrtu['kode_pos_ortu'] : '' }}
                </p>
            </div>
        </div>

        {{-- Dokumen --}}
        <div style="border: 1px solid #e9ecef; border-radius: 8px; margin-bottom: 1.5rem; overflow: hidden;">
            <div style="background: #667eea; color: white; padding: 0.75rem 1rem; display: flex; justify-content: space-between; align-items: center;">
                <span><i class="fas fa-file-alt"></i> Dokumen yang Diunggah</span>
                <a href="{{ route('ppdb.register.step4') }}" style="color: white; font-size: 0.85rem;"><i class="fas fa-edit"></i> Edit</a>
            </div>
            <div style="padding: 1rem;">
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem;">
                    @php
                        $docLabels = [
                            'foto' => 'Pas Foto',
                            'kk' => 'Kartu Keluarga',
                            'akta_lahir' => 'Akta Kelahiran',
                            'ktp_ortu' => 'KTP Orang Tua',
                            'ijazah' => 'Ijazah/SKL',
                            'raport' => 'Raport',
                            'sertifikat_prestasi' => 'Sertifikat Prestasi',
                        ];
                    @endphp
                    @foreach($uploadedDocs as $key => $doc)
                    <div style="background: #d4edda; padding: 0.75rem; border-radius: 4px; text-align: center;">
                        <i class="fas fa-check-circle" style="color: #28a745; font-size: 1.5rem;"></i>
                        <div style="font-size: 0.85rem; margin-top: 0.25rem;">{{ $docLabels[$key] ?? $key }}</div>
                    </div>
                    @endforeach
                </div>
                <p style="margin: 0.75rem 0 0 0; font-size: 0.85rem; color: #666;">
                    <i class="fas fa-info-circle"></i> Total {{ count($uploadedDocs) }} dokumen berhasil diunggah
                </p>
            </div>
        </div>

        {{-- Konfirmasi --}}
        <form method="POST" action="{{ route('ppdb.register.step5.confirm') }}" id="registrationForm">
            @csrf
            
            {{-- Hidden GPS Fields --}}
            <input type="hidden" name="registration_latitude" id="registration_latitude">
            <input type="hidden" name="registration_longitude" id="registration_longitude">
            <input type="hidden" name="registration_altitude" id="registration_altitude">
            <input type="hidden" name="registration_accuracy" id="registration_accuracy">
            
            {{-- Location Status --}}
            <div id="locationStatus" style="background: #e3f2fd; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; display: none;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <i class="fas fa-map-marker-alt" style="color: #1976d2; font-size: 1.5rem;"></i>
                    <div>
                        <div style="font-weight: 600; color: #1976d2;">Lokasi Pendaftaran</div>
                        <small id="locationText" style="color: #666;">Mendeteksi lokasi...</small>
                    </div>
                </div>
            </div>
            
            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; border: 2px solid #667eea;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="display: flex; align-items: flex-start; cursor: pointer;">
                        <input type="checkbox" name="agree" required style="margin-right: 0.75rem; margin-top: 0.25rem;">
                        <span>
                            Saya menyatakan bahwa semua data yang saya isi adalah benar dan dapat dipertanggungjawabkan. 
                            Saya memahami bahwa data yang tidak benar dapat mengakibatkan pembatalan pendaftaran.
                        </span>
                    </label>
                    @error('agree') <small style="color: red;">{{ $message }}</small> @enderror
                </div>
            </div>

            <div style="display: flex; gap: 1rem;">
                <a href="{{ route('ppdb.register.step4') }}" class="btn btn-secondary" style="flex: 1; text-align: center;">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <button type="submit" class="btn btn-success" style="flex: 2; font-size: 1.1rem;" id="submitBtn">
                    <i class="fas fa-paper-plane"></i> Kirim Pendaftaran
                </button>
            </div>
        </form>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>
.btn-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
}
.btn-success:hover {
    background: linear-gradient(135deg, #218838 0%, #1ba87e 100%);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const locationStatus = document.getElementById('locationStatus');
    const locationText = document.getElementById('locationText');
    
    // Try to get GPS location
    if (navigator.geolocation) {
        locationStatus.style.display = 'block';
        locationText.textContent = 'Mendeteksi lokasi GPS...';
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                // Success - fill hidden fields
                document.getElementById('registration_latitude').value = position.coords.latitude;
                document.getElementById('registration_longitude').value = position.coords.longitude;
                document.getElementById('registration_altitude').value = position.coords.altitude || '';
                document.getElementById('registration_accuracy').value = position.coords.accuracy || '';
                
                // Update status
                const accuracy = Math.round(position.coords.accuracy);
                locationText.innerHTML = `
                    <span style="color: #28a745;"><i class="fas fa-check-circle"></i> Lokasi berhasil dideteksi</span><br>
                    <small>Koordinat: ${position.coords.latitude.toFixed(6)}, ${position.coords.longitude.toFixed(6)} (±${accuracy}m)</small>
                `;
                locationStatus.style.background = '#d4edda';
            },
            function(error) {
                // Failed - show warning but allow to continue
                locationText.innerHTML = `
                    <span style="color: #856404;"><i class="fas fa-exclamation-triangle"></i> Lokasi tidak tersedia</span><br>
                    <small>Pendaftaran tetap dapat dilanjutkan tanpa data lokasi.</small>
                `;
                locationStatus.style.background = '#fff3cd';
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    }
});
</script>
@endsection
