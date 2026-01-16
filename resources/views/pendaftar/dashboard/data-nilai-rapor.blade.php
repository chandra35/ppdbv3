@extends('layouts.pendaftar')

@section('title', 'Nilai Rapor')
@section('page-title', 'Nilai Rapor')

@section('breadcrumb')
<li class="breadcrumb-item active">Nilai Rapor</li>
@endsection

@section('css')
<style>
    .nilai-table {
        width: 100%;
        margin-bottom: 1rem;
    }
    .nilai-table th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 12px 8px;
        text-align: center;
        font-weight: 600;
        font-size: 14px;
    }
    .nilai-table td {
        padding: 8px 4px;
        text-align: center;
        vertical-align: middle;
    }
    .nilai-table tbody tr:hover {
        background-color: #f8f9fa;
    }
    .nilai-input {
        width: 100%;
        max-width: 100px;
        padding: 8px 4px;
        border: 2px solid #e9ecef;
        border-radius: 6px;
        text-align: center;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .nilai-input:focus {
        border-color: #667eea;
        outline: none;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    .nilai-input.is-invalid {
        border-color: #dc3545;
    }
    .rata-rata-display {
        font-size: 16px;
        font-weight: bold;
        color: #667eea;
        padding: 8px;
        background: linear-gradient(135deg, #f0f4ff 0%, #e8eeff 100%);
        border-radius: 8px;
        text-align: center;
    }
    .info-box {
        background: #f8f9fa;
        border-left: 3px solid #6c757d;
        padding: 0.75rem 1rem;
        border-radius: 4px;
        margin-bottom: 1.5rem;
    }
    .info-box h5 {
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #495057;
    }
    .info-box ul {
        margin: 0;
        padding-left: 1.2rem;
    }
    .info-box li {
        margin-bottom: 0.25rem;
        font-size: 12px;
        color: #6c757d;
    }
    .info-box li:last-child {
        margin-bottom: 0;
    }
    
    /* Mobile Card Layout */
    .semester-card {
        display: none;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        background: white;
    }
    .semester-card h5 {
        color: #667eea;
        font-weight: bold;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e9ecef;
    }
    .semester-card .form-group {
        margin-bottom: 12px;
    }
    .semester-card label {
        font-weight: 600;
        color: #495057;
        font-size: 13px;
        margin-bottom: 5px;
    }
    .semester-card .nilai-input {
        max-width: 100%;
        font-size: 16px;
        padding: 10px;
    }
    .semester-card .rata-rata-display {
        font-size: 18px;
        padding: 12px;
        margin-top: 10px;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .nilai-table {
            display: none;
        }
        .semester-card {
            display: block;
        }
        .card-header h3 {
            font-size: 16px;
        }
        .info-box h5 {
            font-size: 14px;
        }
        .info-box ul {
            font-size: 12px;
            padding-left: 1.2rem;
        }
        .info-box li {
            margin-bottom: 0.3rem;
        }
        .btn-lg {
            font-size: 14px;
            padding: 10px 16px;
        }
        .small-box .inner h3 {
            font-size: 28px;
        }
        .small-box .inner p {
            font-size: 13px;
        }
    }
    
    @media (max-width: 576px) {
        .card-body {
            padding: 0.75rem;
        }
        .info-box {
            padding: 0.75rem;
            font-size: 12px;
        }
        .semester-card {
            padding: 12px;
        }
        .semester-card h5 {
            font-size: 15px;
            margin-bottom: 12px;
        }
        .semester-card label {
            font-size: 12px;
        }
        .semester-card .nilai-input {
            font-size: 14px;
            padding: 8px;
        }
        .rata-rata-display {
            font-size: 14px;
        }
        .btn-lg {
            font-size: 13px;
            padding: 8px 12px;
        }
    }
    
    /* Upload Rapor Styles */
    .rapor-upload-section {
        min-height: 40px;
    }
    .rapor-upload-section .uploaded-file {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 5px;
    }
    .rapor-upload-section .uploaded-file .btn {
        font-size: 11px;
    }
    .upload-btn-wrapper {
        position: relative;
    }
    .upload-progress {
        margin-top: 5px;
    }
    .upload-progress .progress {
        height: 5px;
    }
    .mobile-rapor-upload {
        background: #f8f9fa;
        padding: 10px;
        border-radius: 6px;
    }
    .uploaded-file-mobile {
        gap: 8px;
        flex-wrap: wrap;
    }
    
    /* Camera Styles for Rapor */
    .camera-container-rapor {
        position: relative;
        width: 100%;
        max-width: 400px;
        margin: 0 auto;
    }
    .camera-container-rapor video {
        width: 100%;
        border-radius: 8px;
        border: 2px solid #667eea;
    }
    .camera-preview-result-rapor {
        max-width: 100%;
        max-height: 250px;
        border-radius: 8px;
        border: 2px solid #28a745;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @if($calonSiswa->is_finalisasi)
        <div class="alert alert-warning">
            <h5><i class="fas fa-lock mr-2"></i>Data Sudah Difinalisasi</h5>
            <p class="mb-0">Nilai rapor sudah difinalisasi dan tidak dapat diubah. Jika terdapat kesalahan data, silakan hubungi panitia.</p>
        </div>
        @endif
        
        <form action="{{ route('pendaftar.nilai-rapor.update') }}" method="POST" id="formNilaiRapor">
            @csrf
            @method('PUT')

            <div class="card">
                <div class="card-header bg-gradient-primary">
                    <h3 class="card-title text-white">
                        <i class="fas fa-graduation-cap mr-2"></i>
                        Input Nilai Rapor Semester 1-5
                        @if($calonSiswa->is_finalisasi)
                        <span class="badge badge-warning ml-2"><i class="fas fa-lock"></i> Terkunci</span>
                        @endif
                    </h3>
                </div>
                <div class="card-body">
                    <div class="info-box">
                        <div style="font-size: 12px; font-weight: 600; margin-bottom: 0.5rem; color: #495057;">
                            <i class="fas fa-info-circle mr-1"></i>Petunjuk Pengisian<br>
                        </div>
                        <ul class="mb-0" style="line-height: 1.8;">
                            <li>Isikan nilai rapor <strong>dari Semester 1 hingga Semester 5</strong> (5 semester SMP).</li>
                            <li>Mata pelajaran yang dinilai: <strong>Matematika, Ilmu Pengetahuan Alam (IPA), dan Ilmu Pengetahuan Sosial (IPS)</strong>.</li>
                            <li>Nilai yang diinput adalah <strong>nilai akhir semester</strong> yang tertera pada raport.</li>
                            <li>Rentang nilai: <strong>1 sampai 100</strong> (angka bulat, tanpa desimal/koma).</li>
                            <li>Rata-rata per semester akan <strong>dihitung otomatis</strong> dari 3 mata pelajaran.</li>
                            <li><strong class="text-primary">Upload file rapor</strong> untuk setiap semester (format: PDF, JPG, PNG, max 5MB).</li>
                            <li>Pastikan semua nilai sudah diisi dengan benar sebelum menyimpan.</li>
                            <li><strong>Nilai rapor berkontribusi 30%</strong> terhadap penilaian akhir PPDB.</li>
                        </ul>
                    </div>

                    <div class="table-responsive">
                        <table class="nilai-table table table-bordered">
                            <thead>
                                <tr>
                                    <th width="12%">Semester</th>
                                    <th width="15%">Matematika</th>
                                    <th width="15%">IPA</th>
                                    <th width="15%">IPS</th>
                                    <th width="18%">Rata-Rata</th>
                                    <th width="25%">File Rapor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($nilaiRapor as $semester => $nilai)
                                <tr>
                                    <td>
                                        <strong>Semester {{ $semester }}</strong>
                                    </td>
                                    <td>
                                        <input type="number" 
                                               name="semester_{{ $semester }}_matematika" 
                                               class="nilai-input form-control @error("semester_{$semester}_matematika") is-invalid @enderror" 
                                               value="{{ old("semester_{$semester}_matematika", $nilai['matematika']) }}"
                                               min="1" 
                                               max="100" 
                                               step="1"
                                               required
                                               data-semester="{{ $semester }}"
                                               data-mapel="matematika"
                                               oninput="calculateRataRata({{ $semester }})">
                                        @error("semester_{$semester}_matematika")
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td>
                                        <input type="number" 
                                               name="semester_{{ $semester }}_ipa" 
                                               class="nilai-input form-control @error("semester_{$semester}_ipa") is-invalid @enderror" 
                                               value="{{ old("semester_{$semester}_ipa", $nilai['ipa']) }}"
                                               min="1" 
                                               max="100" 
                                               step="1"
                                               required
                                               data-semester="{{ $semester }}"
                                               data-mapel="ipa"
                                               oninput="calculateRataRata({{ $semester }})">
                                        @error("semester_{$semester}_ipa")
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td>
                                        <input type="number" 
                                               name="semester_{{ $semester }}_ips" 
                                               class="nilai-input form-control @error("semester_{$semester}_ips") is-invalid @enderror" 
                                               value="{{ old("semester_{$semester}_ips", $nilai['ips']) }}"
                                               min="1" 
                                               max="100" 
                                               step="1"
                                               required
                                               data-semester="{{ $semester }}"
                                               data-mapel="ips"
                                               oninput="calculateRataRata({{ $semester }})">
                                        @error("semester_{$semester}_ips")
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td>
                                        <div class="rata-rata-display" id="rata_rata_{{ $semester }}">
                                            {{ $nilai['rata_rata'] ? number_format($nilai['rata_rata'], 2) : '-' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="rapor-upload-section" id="rapor_upload_{{ $semester }}">
                                            @if($nilai['dokumen'])
                                                <div class="uploaded-file">
                                                    <a href="{{ asset('storage/' . $nilai['dokumen']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-success mb-1" title="Lihat File">
                                                        <i class="fas fa-file-pdf"></i> {{ Str::limit($nilai['dokumen']->nama_file, 15) }}
                                                    </a>
                                                    <br>
                                                    <span class="badge badge-{{ $nilai['status_validasi'] == 'valid' ? 'success' : ($nilai['status_validasi'] == 'invalid' ? 'danger' : 'warning') }}">
                                                        <i class="fas fa-{{ $nilai['status_validasi'] == 'valid' ? 'check' : ($nilai['status_validasi'] == 'invalid' ? 'times' : 'clock') }}"></i>
                                                        {{ ucfirst($nilai['status_validasi']) }}
                                                    </span>
                                                    @if($nilai['status_validasi'] == 'invalid' && $nilai['catatan_validasi'])
                                                        <br><small class="text-danger">{{ $nilai['catatan_validasi'] }}</small>
                                                    @endif
                                                    @if(!$calonSiswa->is_finalisasi)
                                                    <button type="button" class="btn btn-sm btn-outline-danger mt-1" onclick="deleteRapor({{ $semester }})" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    @endif
                                                </div>
                                            @else
                                                @if(!$calonSiswa->is_finalisasi)
                                                <div class="upload-btn-wrapper">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="openUploadRaporModal({{ $semester }})">
                                                        <i class="fas fa-upload"></i> Upload
                                                    </button>
                                                </div>
                                                @else
                                                <span class="text-muted"><i class="fas fa-times-circle"></i> Tidak ada</span>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr style="background: #f8f9fa;">
                                    <td colspan="5" class="text-right"><strong>Rata-Rata Keseluruhan:</strong></td>
                                    <td>
                                        <div class="rata-rata-display" id="rata_rata_keseluruhan" style="font-size: 20px; color: #28a745;">
                                            {{ $calonSiswa->rata_rata_rapor ? number_format($calonSiswa->rata_rata_rapor, 2) : '-' }}
                                        </div>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    @foreach($nilaiRapor as $semester => $nilai)
                    <div class="semester-card">
                        <h5><i class="fas fa-book mr-2"></i>Semester {{ $semester }}</h5>
                        
                        <div class="form-group">
                            <label for="mobile_semester_{{ $semester }}_matematika">
                                <i class="fas fa-calculator mr-1"></i> Matematika
                            </label>
                            <input type="number" 
                                   id="mobile_semester_{{ $semester }}_matematika"
                                   name="semester_{{ $semester }}_matematika" 
                                   class="nilai-input form-control @error("semester_{$semester}_matematika") is-invalid @enderror" 
                                   value="{{ old("semester_{$semester}_matematika", $nilai['matematika']) }}"
                                   min="1" 
                                   max="100" 
                                   step="1"
                                   required
                                   data-semester="{{ $semester }}"
                                   data-mapel="matematika"
                                   oninput="calculateRataRata({{ $semester }})">
                            @error("semester_{$semester}_matematika")
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="mobile_semester_{{ $semester }}_ipa">
                                <i class="fas fa-flask mr-1"></i> IPA
                            </label>
                            <input type="number" 
                                   id="mobile_semester_{{ $semester }}_ipa"
                                   name="semester_{{ $semester }}_ipa" 
                                   class="nilai-input form-control @error("semester_{$semester}_ipa") is-invalid @enderror" 
                                   value="{{ old("semester_{$semester}_ipa", $nilai['ipa']) }}"
                                   min="1" 
                                   max="100" 
                                   step="1"
                                   required
                                   data-semester="{{ $semester }}"
                                   data-mapel="ipa"
                                   oninput="calculateRataRata({{ $semester }})">
                            @error("semester_{$semester}_ipa")
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="mobile_semester_{{ $semester }}_ips">
                                <i class="fas fa-globe mr-1"></i> IPS
                            </label>
                            <input type="number" 
                                   id="mobile_semester_{{ $semester }}_ips"
                                   name="semester_{{ $semester }}_ips" 
                                   class="nilai-input form-control @error("semester_{$semester}_ips") is-invalid @enderror" 
                                   value="{{ old("semester_{$semester}_ips", $nilai['ips']) }}"
                                   min="1" 
                                   max="100" 
                                   step="1"
                                   required
                                   data-semester="{{ $semester }}"
                                   data-mapel="ips"
                                   oninput="calculateRataRata({{ $semester }})">
                            @error("semester_{$semester}_ips")
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-0">
                            <label><i class="fas fa-chart-line mr-1"></i> Rata-Rata</label>
                            <div class="rata-rata-display" id="mobile_rata_rata_{{ $semester }}">
                                {{ $nilai['rata_rata'] ? number_format($nilai['rata_rata'], 2) : '-' }}
                            </div>
                        </div>

                        {{-- Upload Rapor Section for Mobile --}}
                        <div class="form-group mt-3" style="border-top: 1px dashed #ddd; padding-top: 12px;">
                            <label><i class="fas fa-file-pdf mr-1"></i> File Rapor</label>
                            <div class="mobile-rapor-upload" id="mobile_rapor_upload_{{ $semester }}">
                                @if($nilai['dokumen'])
                                    <div class="uploaded-file-mobile">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <a href="{{ asset('storage/' . $nilai['dokumen']->file_path) }}" target="_blank" class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-file-pdf"></i> Lihat File
                                            </a>
                                            @if(!$calonSiswa->is_finalisasi)
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteRapor({{ $semester }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="badge badge-{{ $nilai['status_validasi'] == 'valid' ? 'success' : ($nilai['status_validasi'] == 'invalid' ? 'danger' : 'warning') }}">
                                                <i class="fas fa-{{ $nilai['status_validasi'] == 'valid' ? 'check' : ($nilai['status_validasi'] == 'invalid' ? 'times' : 'clock') }}"></i>
                                                {{ ucfirst($nilai['status_validasi']) }}
                                            </span>
                                            @if($nilai['status_validasi'] == 'invalid' && $nilai['catatan_validasi'])
                                                <br><small class="text-danger">{{ $nilai['catatan_validasi'] }}</small>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    @if(!$calonSiswa->is_finalisasi)
                                    <div class="upload-btn-wrapper">
                                        <button type="button" class="btn btn-block btn-outline-primary" onclick="openUploadRaporModal({{ $semester }})">
                                            <i class="fas fa-upload mr-1"></i> Upload Rapor
                                        </button>
                                    </div>
                                    @else
                                    <span class="text-muted"><i class="fas fa-times-circle"></i> Tidak ada file</span>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach

                    <!-- Mobile Overall Average -->
                    <div class="semester-card" style="background: #f8f9fa; border: 2px solid #667eea;">
                        <h5 class="text-center mb-3" style="color: #28a745;">
                            <i class="fas fa-trophy mr-2"></i>Rata-Rata Keseluruhan
                        </h5>
                        <div class="rata-rata-display" id="mobile_rata_rata_keseluruhan" style="font-size: 24px; color: #28a745;">
                            {{ $calonSiswa->rata_rata_rapor ? number_format($calonSiswa->rata_rata_rapor, 2) : '-' }}
                        </div>
                    </div>

                </div>
                @if(!$calonSiswa->is_finalisasi)
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save mr-1"></i> Simpan Nilai Rapor
                    </button>
                    <a href="{{ route('pendaftar.dashboard') }}" class="btn btn-secondary btn-lg">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                </div>
                @endif
            </div>
        </form>
    </div>

    <!-- Info Card -->
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header bg-info">
                <h3 class="card-title text-white">
                    <i class="fas fa-calculator mr-2"></i>
                    Informasi Perhitungan Nilai
                </h3>
            </div>
            <div class="card-body">
                <h5>Komponen Penilaian Akhir PPDB:</h5>
                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="small-box bg-gradient-primary">
                            <div class="inner">
                                <h3>30%</h3>
                                <p>Nilai Rapor</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-book"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="small-box bg-gradient-success">
                            <div class="inner">
                                <h3>40%</h3>
                                <p>Tes CBT</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-laptop"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="small-box bg-gradient-warning">
                            <div class="inner">
                                <h3>30%</h3>
                                <p>Wawancara</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-comments"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="text-muted mb-0">
                    <i class="fas fa-info-circle mr-1"></i>
                    Nilai akhir = (Rata-rata Rapor × 30%) + (Nilai CBT × 40%) + (Nilai Wawancara × 30%)
                </p>
            </div>
        </div>
    </div>
</div>

{{-- Modal Upload Rapor dengan Kamera --}}
<div class="modal fade" id="uploadRaporModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-upload mr-2"></i>
                    Upload Rapor <span id="raporSemesterLabel">Semester 1</span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="currentSemester" value="">
                
                {{-- Source Selection --}}
                <div class="form-group">
                    <label class="font-weight-bold">Sumber File</label>
                    <div class="d-flex">
                        <div class="custom-control custom-radio mr-4">
                            <input type="radio" id="raporSourceFile" name="raporUploadSource" class="custom-control-input" value="file" checked>
                            <label class="custom-control-label" for="raporSourceFile">
                                <i class="fas fa-file-upload mr-1"></i> Pilih File
                            </label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input type="radio" id="raporSourceCamera" name="raporUploadSource" class="custom-control-input" value="camera">
                            <label class="custom-control-label" for="raporSourceCamera">
                                <i class="fas fa-camera mr-1"></i> Kamera
                            </label>
                        </div>
                    </div>
                </div>
                
                {{-- File Input Section --}}
                <div id="raporFileSection">
                    <div class="form-group">
                        <label>Pilih File Rapor</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="raporFileInput" accept=".pdf,.jpg,.jpeg,.png">
                            <label class="custom-file-label" for="raporFileInput">Pilih file...</label>
                        </div>
                        <small class="text-muted">Format: PDF, JPG, JPEG, PNG. Maks: 5MB</small>
                    </div>
                    <div id="raporFilePreview" class="text-center" style="display: none;">
                        <img id="raporPreviewImage" src="" style="max-width: 100%; max-height: 200px; border-radius: 8px;">
                    </div>
                </div>
                
                {{-- Camera Section --}}
                <div id="raporCameraSection" style="display: none;">
                    <div class="camera-container-rapor mb-3">
                        <video id="raporCameraVideo" autoplay playsinline></video>
                        <canvas id="raporCameraCanvas" style="display: none;"></canvas>
                    </div>
                    <div class="text-center mb-3" id="raporCameraControls" style="display: none;">
                        <button type="button" class="btn btn-primary btn-lg" id="btnCaptureRapor">
                            <i class="fas fa-camera"></i> Ambil Foto
                        </button>
                        <button type="button" class="btn btn-secondary btn-lg" id="btnRetakeRapor" style="display: none;">
                            <i class="fas fa-redo"></i> Ulangi
                        </button>
                    </div>
                    <div class="text-center" id="raporCameraStartBtn">
                        <button type="button" class="btn btn-primary" id="btnStartRaporCamera">
                            <i class="fas fa-video"></i> Mulai Kamera
                        </button>
                    </div>
                    <div id="raporCapturedPreview" class="text-center mt-3" style="display: none;">
                        <p class="text-success mb-2"><i class="fas fa-check-circle"></i> Foto berhasil diambil</p>
                        <img id="raporCapturedImage" src="" class="camera-preview-result-rapor">
                    </div>
                </div>
                
                <input type="hidden" id="raporCameraCaptured" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnUploadRapor" disabled>
                    <i class="fas fa-upload mr-1"></i> Upload
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    // Disable all inputs if finalized
    @if($calonSiswa->is_finalisasi)
    $('.nilai-input').prop('readonly', true);
    @endif
    
    // Calculate rata-rata on page load
    for (let i = 1; i <= 5; i++) {
        calculateRataRata(i);
    }
    calculateRataRataKeseluruhan();
    
    // Sync values between desktop and mobile inputs
    $('.nilai-input').on('input', function() {
        const name = $(this).attr('name');
        const value = $(this).val();
        
        // Update all inputs with the same name
        $(`input[name="${name}"]`).val(value);
    });
});

function calculateRataRata(semester) {
    const mtk = parseFloat($(`input[name="semester_${semester}_matematika"]`).val()) || 0;
    const ipa = parseFloat($(`input[name="semester_${semester}_ipa"]`).val()) || 0;
    const ips = parseFloat($(`input[name="semester_${semester}_ips"]`).val()) || 0;
    
    if (mtk > 0 && ipa > 0 && ips > 0) {
        const rataRata = (mtk + ipa + ips) / 3;
        $(`#rata_rata_${semester}`).text(rataRata.toFixed(2));
        $(`#mobile_rata_rata_${semester}`).text(rataRata.toFixed(2));
    } else {
        $(`#rata_rata_${semester}`).text('-');
        $(`#mobile_rata_rata_${semester}`).text('-');
    }
    
    // Recalculate overall average
    calculateRataRataKeseluruhan();
}

function calculateRataRataKeseluruhan() {
    let total = 0;
    let count = 0;
    
    for (let i = 1; i <= 5; i++) {
        const mtk = parseFloat($(`input[name="semester_${i}_matematika"]`).val()) || 0;
        const ipa = parseFloat($(`input[name="semester_${i}_ipa"]`).val()) || 0;
        const ips = parseFloat($(`input[name="semester_${i}_ips"]`).val()) || 0;
        
        if (mtk > 0 && ipa > 0 && ips > 0) {
            total += (mtk + ipa + ips) / 3;
            count++;
        }
    }
    
    if (count > 0) {
        const rataRataKeseluruhan = total / count;
        $('#rata_rata_keseluruhan').text(rataRataKeseluruhan.toFixed(2));
        $('#mobile_rata_rata_keseluruhan').text(rataRataKeseluruhan.toFixed(2));
    } else {
        $('#rata_rata_keseluruhan').text('-');
        $('#mobile_rata_rata_keseluruhan').text('-');
    }
}

// Validate input range
$('.nilai-input').on('input', function() {
    const val = parseInt($(this).val());
    if (val < 1) {
        $(this).val(1);
    } else if (val > 100) {
        $(this).val(100);
    }
});

// Form validation
$('#formNilaiRapor').on('submit', function(e) {
    let isValid = true;
    let emptyFields = [];
    
    $('.nilai-input').each(function() {
        const val = parseInt($(this).val());
        if (!val || val < 1 || val > 100) {
            isValid = false;
            $(this).addClass('is-invalid');
            emptyFields.push($(this).attr('name'));
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    if (!isValid) {
        e.preventDefault();
        toastr.error('Mohon isi semua nilai dengan benar (1-100)');
        return false;
    }
});

// Upload Rapor per Semester
function uploadRapor(semester, source = 'desktop') {
    const fileInput = source === 'mobile' ? $(`#mobile_rapor_file_${semester}`)[0] : $(`#rapor_file_${semester}`)[0];
    const file = fileInput.files[0];
    
    if (!file) {
        toastr.error('Pilih file terlebih dahulu');
        return;
    }
    
    // Validate file size (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
        toastr.error('Ukuran file maksimal 5MB');
        fileInput.value = '';
        return;
    }
    
    // Validate file type
    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
    if (!allowedTypes.includes(file.type)) {
        toastr.error('Format file harus PDF, JPG, JPEG, atau PNG');
        fileInput.value = '';
        return;
    }
    
    const formData = new FormData();
    formData.append('file', file);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
    
    // Show loading
    const uploadSection = $(`#rapor_upload_${semester}`);
    const mobileUploadSection = $(`#mobile_rapor_upload_${semester}`);
    uploadSection.html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Uploading...</div>');
    mobileUploadSection.html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Uploading...</div>');
    
    $.ajax({
        url: `{{ url('pendaftar/nilai-rapor/upload-rapor') }}/${semester}`,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                // Refresh the upload section with new file info
                const fileHtml = `
                    <div class="uploaded-file">
                        <a href="${response.dokumen.file_url}" target="_blank" class="btn btn-sm btn-outline-success mb-1" title="Lihat File">
                            <i class="fas fa-file-pdf"></i> ${response.dokumen.nama_file.substring(0, 15)}...
                        </a>
                        <span class="badge badge-warning">Pending</span>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteRapor(${semester})" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
                uploadSection.html(fileHtml);
                
                const mobileFileHtml = `
                    <div class="uploaded-file-mobile d-flex align-items-center justify-content-between">
                        <a href="${response.dokumen.file_url}" target="_blank" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-file-pdf"></i> Lihat File
                        </a>
                        <span class="badge badge-warning">Pending</span>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteRapor(${semester})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
                mobileUploadSection.html(mobileFileHtml);
            } else {
                toastr.error(response.message);
                resetUploadSection(semester);
            }
        },
        error: function(xhr) {
            let errorMessage = 'Terjadi kesalahan saat upload file';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                errorMessage = Object.values(xhr.responseJSON.errors).flat().join(', ');
            }
            toastr.error(errorMessage);
            resetUploadSection(semester);
        }
    });
}

// Delete Rapor
function deleteRapor(semester) {
    if (!confirm('Yakin ingin menghapus file rapor semester ' + semester + '?')) {
        return;
    }
    
    const uploadSection = $(`#rapor_upload_${semester}`);
    const mobileUploadSection = $(`#mobile_rapor_upload_${semester}`);
    
    $.ajax({
        url: `{{ url('pendaftar/nilai-rapor/delete-rapor') }}/${semester}`,
        type: 'DELETE',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                resetUploadSection(semester);
            } else {
                toastr.error(response.message);
            }
        },
        error: function(xhr) {
            let errorMessage = 'Terjadi kesalahan saat menghapus file';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            toastr.error(errorMessage);
        }
    });
}

// Reset upload section to initial state
function resetUploadSection(semester) {
    const uploadHtml = `
        <div class="upload-btn-wrapper">
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="openUploadRaporModal(${semester})">
                <i class="fas fa-upload"></i> Upload
            </button>
        </div>
    `;
    $(`#rapor_upload_${semester}`).html(uploadHtml);
    
    const mobileUploadHtml = `
        <div class="upload-btn-wrapper">
            <button type="button" class="btn btn-block btn-outline-primary" onclick="openUploadRaporModal(${semester})">
                <i class="fas fa-upload mr-1"></i> Upload Rapor
            </button>
        </div>
    `;
    $(`#mobile_rapor_upload_${semester}`).html(mobileUploadHtml);
}

// ================================
// UPLOAD RAPOR MODAL WITH CAMERA
// ================================
let raporCameraStream = null;

function openUploadRaporModal(semester) {
    $('#currentSemester').val(semester);
    $('#raporSemesterLabel').text('Semester ' + semester);
    
    // Reset modal state
    $('#raporSourceFile').prop('checked', true);
    $('#raporFileSection').show();
    $('#raporCameraSection').hide();
    $('#raporFileInput').val('');
    $('.custom-file-label').text('Pilih file...');
    $('#raporFilePreview').hide();
    $('#raporCameraCaptured').val('');
    $('#btnUploadRapor').prop('disabled', true);
    stopRaporCamera();
    
    $('#uploadRaporModal').modal('show');
}

// Toggle file/camera source
$('input[name="raporUploadSource"]').on('change', function() {
    const source = $(this).val();
    if (source === 'file') {
        $('#raporFileSection').show();
        $('#raporCameraSection').hide();
        stopRaporCamera();
        $('#raporCameraCaptured').val('');
    } else {
        $('#raporFileSection').hide();
        $('#raporCameraSection').show();
        $('#raporFileInput').val('');
        $('.custom-file-label').text('Pilih file...');
        $('#raporFilePreview').hide();
        $('#btnUploadRapor').prop('disabled', true);
    }
});

// File input change
$('#raporFileInput').on('change', function() {
    const file = this.files[0];
    if (file) {
        if (file.size > 5 * 1024 * 1024) {
            toastr.error('Ukuran file maksimal 5MB');
            this.value = '';
            return;
        }
        
        $('.custom-file-label').text(file.name);
        $('#btnUploadRapor').prop('disabled', false);
        
        // Preview for images
        if (file.type.match(/image\/(jpg|jpeg|png)/i)) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#raporPreviewImage').attr('src', e.target.result);
                $('#raporFilePreview').show();
            };
            reader.readAsDataURL(file);
        } else {
            $('#raporFilePreview').hide();
        }
    }
});

// Camera functions
function stopRaporCamera() {
    if (raporCameraStream) {
        raporCameraStream.getTracks().forEach(track => track.stop());
        raporCameraStream = null;
    }
    $('#raporCameraVideo')[0].srcObject = null;
    $('#raporCameraControls').hide();
    $('#raporCameraStartBtn').show();
    $('#raporCapturedPreview').hide();
    $('#btnCaptureRapor').show();
    $('#btnRetakeRapor').hide();
}

$('#btnStartRaporCamera').on('click', async function() {
    try {
        const constraints = {
            video: { 
                facingMode: 'environment',
                width: { ideal: 1280 },
                height: { ideal: 720 }
            }
        };
        raporCameraStream = await navigator.mediaDevices.getUserMedia(constraints);
        $('#raporCameraVideo')[0].srcObject = raporCameraStream;
        $('#raporCameraStartBtn').hide();
        $('#raporCameraControls').show();
        $('#btnCaptureRapor').show();
        $('#btnRetakeRapor').hide();
    } catch (err) {
        console.error('Camera error:', err);
        toastr.error('Gagal mengakses kamera. Pastikan izin kamera diberikan.');
    }
});

$('#btnCaptureRapor').on('click', function() {
    const video = $('#raporCameraVideo')[0];
    const canvas = $('#raporCameraCanvas')[0];
    
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);
    
    const imageData = canvas.toDataURL('image/jpeg', 0.85);
    $('#raporCameraCaptured').val(imageData);
    $('#raporCapturedImage').attr('src', imageData);
    $('#raporCapturedPreview').show();
    
    stopRaporCamera();
    $('#raporCameraStartBtn').hide();
    $('#raporCameraControls').show();
    $('#btnCaptureRapor').hide();
    $('#btnRetakeRapor').show();
    
    $('#btnUploadRapor').prop('disabled', false);
});

$('#btnRetakeRapor').on('click', function() {
    $('#raporCameraCaptured').val('');
    $('#raporCapturedPreview').hide();
    $('#btnUploadRapor').prop('disabled', true);
    $('#raporCameraStartBtn').show();
    $('#raporCameraControls').hide();
});

// Upload button click
$('#btnUploadRapor').on('click', function() {
    const semester = $('#currentSemester').val();
    const source = $('input[name="raporUploadSource"]:checked').val();
    
    const formData = new FormData();
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
    
    if (source === 'file') {
        const file = $('#raporFileInput')[0].files[0];
        if (!file) {
            toastr.error('Pilih file terlebih dahulu');
            return;
        }
        formData.append('file', file);
    } else {
        const capturedImage = $('#raporCameraCaptured').val();
        if (!capturedImage) {
            toastr.error('Ambil foto terlebih dahulu');
            return;
        }
        formData.append('camera_captured', capturedImage);
    }
    
    const btn = $(this);
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Uploading...');
    
    $.ajax({
        url: `{{ url('pendaftar/nilai-rapor/upload-rapor') }}/${semester}`,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                $('#uploadRaporModal').modal('hide');
                
                // Refresh the upload section
                const fileHtml = `
                    <div class="uploaded-file">
                        <a href="${response.dokumen.file_url}" target="_blank" class="btn btn-sm btn-outline-success mb-1">
                            <i class="fas fa-file-pdf"></i> ${response.dokumen.nama_file.substring(0, 15)}...
                        </a>
                        <span class="badge badge-warning">Pending</span>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteRapor(${semester})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
                $(`#rapor_upload_${semester}`).html(fileHtml);
                
                const mobileFileHtml = `
                    <div class="uploaded-file-mobile d-flex align-items-center justify-content-between">
                        <a href="${response.dokumen.file_url}" target="_blank" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-file-pdf"></i> Lihat File
                        </a>
                        <span class="badge badge-warning">Pending</span>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteRapor(${semester})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
                $(`#mobile_rapor_upload_${semester}`).html(mobileFileHtml);
            } else {
                toastr.error(response.message);
            }
        },
        error: function(xhr) {
            let errorMessage = 'Terjadi kesalahan saat upload file';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            toastr.error(errorMessage);
        },
        complete: function() {
            btn.prop('disabled', false).html('<i class="fas fa-upload mr-1"></i> Upload');
        }
    });
});

// Stop camera when modal closes
$('#uploadRaporModal').on('hidden.bs.modal', function() {
    stopRaporCamera();
});
</script>
@endsection
