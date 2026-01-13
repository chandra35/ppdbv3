@extends('layouts.pendaftar')

@section('title', 'Upload Dokumen')
@section('page-title', 'Upload Dokumen')

@section('breadcrumb')
<li class="breadcrumb-item active">Upload Dokumen</li>
@endsection

@section('css')
<link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
<style>
    .doc-card {
        border: 2px dashed #ddd;
        border-radius: 10px;
        padding: 1.5rem;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
        height: 100%;
        position: relative;
        min-height: 200px;
    }
    
    .doc-card:hover {
        border-color: #667eea;
        background: #f8f9ff;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .doc-card.uploaded {
        border-color: #48bb78;
        border-style: solid;
        background: #f0fff4;
    }
    
    /* Status-based styling */
    .doc-card.status-revision {
        border-color: #f59e0b !important;
        border-width: 3px !important;
        border-style: solid !important;
        background: #fffbeb !important;
        animation: pulse-warning 2s ease-in-out infinite;
    }
    
    @keyframes pulse-warning {
        0%, 100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4); }
        50% { box-shadow: 0 0 0 8px rgba(245, 158, 11, 0); }
    }
    
    .doc-card.status-pending {
        border-color: #3b82f6;
        background: #eff6ff;
    }
    
    .doc-card.status-valid {
        border-color: #10b981;
        background: #ecfdf5;
    }
    
    .doc-card .thumbnail-preview {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 100px;
        overflow: hidden;
        border-radius: 8px 8px 0 0;
        background: #f8f9fa;
    }
    
    .doc-card .thumbnail-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .doc-card.uploaded.has-thumbnail {
        padding-top: 110px;
    }
    
    .doc-card .icon {
        font-size: 2.5rem;
        color: #999;
        margin-bottom: 0.75rem;
    }
    
    .doc-card.uploaded .icon {
        color: #48bb78;
    }
    
    .doc-card.status-revision .icon {
        color: #f59e0b;
        animation: shake 0.5s ease-in-out infinite;
    }
    
    @keyframes shake {
        0%, 100% { transform: rotate(0deg); }
        25% { transform: rotate(-5deg); }
        75% { transform: rotate(5deg); }
    }
    
    .doc-card h5 {
        margin-bottom: 0.75rem;
        font-size: 1rem;
        font-weight: 600;
        color: #1f2937;
    }
    
    .doc-card .status {
        font-size: 0.875rem;
        font-weight: 600;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        display: inline-block;
        margin-top: 0.5rem;
    }
    
    .doc-card .status.pending {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .doc-card .status.valid {
        background: #d1fae5;
        color: #065f46;
    }
    
    .doc-card .status.revision {
        background: #fef3c7;
        color: #92400e;
        border: 2px solid #f59e0b;
        animation: blink 1.5s ease-in-out infinite;
    }
    
    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
    
    .doc-card .catatan-revisi {
        margin-top: 0.75rem;
        padding: 0.75rem;
        background: #fff;
        border-left: 4px solid #f59e0b;
        text-align: left;
        font-size: 0.813rem;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .doc-card .catatan-revisi strong {
        color: #92400e;
        display: block;
        margin-bottom: 0.25rem;
    }
    
    .doc-card .catatan-revisi p {
        margin: 0;
        color: #78350f;
        line-height: 1.5;
    }
    
    .preview-img {
        max-width: 100%;
        max-height: 250px;
        object-fit: contain;
        border-radius: 8px;
        margin-top: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .btn-delete-doc {
        position: absolute;
        top: 10px;
        right: 10px;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .doc-card {
            min-height: 180px;
            padding: 1.25rem;
        }
        
        .doc-card .icon {
            font-size: 2rem;
        }
        
        .doc-card h5 {
            font-size: 0.938rem;
        }
    }
    
    /* Background for revision notes */
    .bg-warning-light {
        background-color: #fffbeb !important;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        @if($calonSiswa->is_finalisasi)
        <div class="alert alert-warning">
            <h5><i class="fas fa-lock mr-2"></i>Data Sudah Difinalisasi</h5>
            <p class="mb-0">Dokumen sudah difinalisasi dan tidak dapat diubah. Jika terdapat kesalahan dokumen, silakan hubungi panitia.</p>
        </div>
        @endif
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-upload mr-2"></i>
                    Upload Dokumen Persyaratan
                    @if($calonSiswa->is_finalisasi)
                    <span class="badge badge-warning ml-2"><i class="fas fa-lock"></i> Terkunci</span>
                    @endif
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Ketentuan Upload:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Format file: PDF, JPG, JPEG, PNG</li>
                        <li>Ukuran maksimal: 2MB per file</li>
                        <li>Pastikan dokumen jelas dan dapat dibaca</li>
                    </ul>
                </div>

                <div class="row">
                    @foreach($requiredDocs as $key => $label)
                        @php
                            $doc = $uploadedDocs->get($key);
                            $isUploaded = $doc !== null;
                            $isImage = false;
                            $statusClass = '';
                            if ($isUploaded) {
                                $extension = strtolower(pathinfo($doc->file_path, PATHINFO_EXTENSION));
                                $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);
                                $statusClass = 'status-' . $doc->status_verifikasi;
                            }
                        @endphp
                        <div class="col-lg-3 col-md-4 col-sm-6 col-12 mb-4">
                            <div class="doc-card {{ $isUploaded ? 'uploaded' : '' }} {{ $isImage ? 'has-thumbnail' : '' }} {{ $statusClass }}" 
                                 {{ !$calonSiswa->is_finalisasi ? 'data-toggle=modal' : '' }}
                                 {{ !$calonSiswa->is_finalisasi ? 'data-target=#uploadModal' : '' }}
                                 data-doc-type="{{ $key }}"
                                 data-doc-label="{{ $label }}"
                                 @if($isUploaded)
                                 data-doc-id="{{ $doc->id }}"
                                 data-doc-path="{{ asset('storage/' . $doc->file_path) }}"
                                 data-doc-name="{{ $doc->nama_file }}"
                                 @endif
                                 style="{{ $calonSiswa->is_finalisasi ? 'cursor: default;' : '' }}">
                                @if($isUploaded && $isImage)
                                    <div class="thumbnail-preview">
                                        <img src="{{ asset('storage/' . $doc->file_path) }}" alt="{{ $label }}">
                                    </div>
                                @endif
                                <div class="icon">
                                    @if($isUploaded)
                                        @if($doc->status_verifikasi === 'revision')
                                            <i class="fas fa-exclamation-triangle"></i>
                                        @elseif($doc->status_verifikasi === 'valid')
                                            <i class="fas fa-check-circle"></i>
                                        @else
                                            <i class="fas fa-file-check"></i>
                                        @endif
                                    @else
                                        <i class="fas fa-cloud-upload-alt"></i>
                                    @endif
                                </div>
                                <h5>{{ $label }}</h5>
                                @if($isUploaded)
                                    <span class="status {{ $doc->status_verifikasi }}">
                                        @if($doc->status_verifikasi === 'pending')
                                            <i class="fas fa-clock"></i> Menunggu Verifikasi
                                        @elseif($doc->status_verifikasi === 'valid')
                                            <i class="fas fa-check-circle"></i> Terverifikasi
                                        @elseif($doc->status_verifikasi === 'revision')
                                            <i class="fas fa-exclamation-circle"></i> Perlu Revisi
                                        @else
                                            <i class="fas fa-clock"></i> {{ ucfirst($doc->status_verifikasi) }}
                                        @endif
                                    </span>
                                    
                                    @if($doc->status_verifikasi === 'revision' && $doc->catatan_revisi)
                                        <div class="catatan-revisi">
                                            <strong><i class="fas fa-info-circle"></i> Catatan Admin:</strong>
                                            <p>{{ $doc->catatan_revisi }}</p>
                                        </div>
                                    @endif
                                @else
                                    <span class="text-muted"><i class="fas fa-upload"></i> Belum diupload</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        {{-- Section Dokumen Tambahan --}}
        @if($izinkanDokumenTambahan)
        <div class="card card-outline card-success mt-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Dokumen Tambahan (Opsional)
                </h3>
                @if(!$calonSiswa->is_finalisasi)
                <div class="card-tools">
                    <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#uploadDokumenTambahanModal">
                        <i class="fas fa-plus mr-1"></i> Tambah Dokumen
                    </button>
                </div>
                @endif
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-3" style="font-size: 0.9rem;">
                    <i class="fas fa-info-circle mr-1"></i>
                    Upload dokumen pendukung seperti: Sertifikat Prestasi, KIP/PIP, SKTM, Piagam Penghargaan, dll.
                    <br><small class="text-muted">Format: PDF, JPG, JPEG, PNG. Maksimal 5MB per file.</small>
                </div>
                
                @if($uploadedDokumenTambahan->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="30%">Jenis</th>
                                <th width="35%">Nama File</th>
                                <th width="30%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($uploadedDokumenTambahan as $idx => $dok)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td>
                                    <strong>{{ $dokumenTambahanOptions[$dok->jenis_dokumen] ?? $dok->jenis_dokumen }}</strong>
                                    @if($dok->nama_dokumen && $dok->nama_dokumen != ($dokumenTambahanOptions[$dok->jenis_dokumen] ?? ''))
                                    <br><small class="text-muted">{{ Str::limit($dok->nama_dokumen, 40) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <i class="fas fa-file-{{ Str::endsWith($dok->nama_file, '.pdf') ? 'pdf text-danger' : 'image text-info' }} mr-1"></i>
                                    {{ Str::limit($dok->nama_file, 25) }}
                                    <br><small class="text-muted">{{ $dok->file_size_formatted }}</small>
                                </td>
                                <td>
                                    @php
                                        $extension = strtolower(pathinfo($dok->file_path, PATHINFO_EXTENSION));
                                        $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);
                                    @endphp
                                    <button type="button" class="btn btn-xs btn-info btn-preview-dokumen-tambahan" 
                                            data-url="{{ asset('storage/' . $dok->file_path) }}"
                                            data-title="{{ $dokumenTambahanOptions[$dok->jenis_dokumen] ?? $dok->jenis_dokumen }}"
                                            data-type="{{ $isImage ? 'image' : 'pdf' }}"
                                            title="Lihat">
                                        <i class="fas fa-eye"></i> Lihat
                                    </button>
                                    @if(!$calonSiswa->is_finalisasi)
                                    <button type="button" class="btn btn-xs btn-danger btn-delete-dokumen-tambahan" 
                                            data-id="{{ $dok->id }}" 
                                            data-nama="{{ $dok->nama_file }}" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center text-muted py-4">
                    <i class="fas fa-folder-open fa-3x mb-3"></i>
                    <p class="mb-0">Belum ada dokumen tambahan yang diupload</p>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clipboard-list mr-2"></i>
                    Checklist Dokumen
                </h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($requiredDocs as $key => $label)
                        @php $doc = $uploadedDocs->get($key); @endphp
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>
                                @if($doc && $doc->status_verifikasi === 'revision')
                                    <i class="fas fa-exclamation-circle text-warning mr-1"></i>
                                @endif
                                {{ $label }}
                            </span>
                            @if($doc)
                                @if($doc->status_verifikasi === 'valid')
                                    <span class="badge badge-success">
                                        <i class="fas fa-check-circle"></i> Disetujui
                                    </span>
                                @elseif($doc->status_verifikasi === 'revision')
                                    <span class="badge badge-warning" style="background-color: #f59e0b; border-color: #f59e0b;">
                                        <i class="fas fa-exclamation-circle"></i> Perlu Revisi
                                    </span>
                                @else
                                    <span class="badge badge-info">
                                        <i class="fas fa-clock"></i> Proses
                                    </span>
                                @endif
                            @else
                                <span class="badge badge-secondary"><i class="fas fa-minus"></i> Belum</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="card-footer">
                <small class="text-muted">
                    <i class="fas fa-check-double mr-1"></i>
                    {{ $uploadedDocs->count() }} dari {{ count($requiredDocs) }} dokumen terupload
                </small>
                <div class="progress mt-2" style="height: 10px;">
                    @php
                        $verifiedCount = $uploadedDocs->where('status_verifikasi', 'valid')->count();
                        $percentage = count($requiredDocs) > 0 ? ($verifiedCount / count($requiredDocs)) * 100 : 0;
                    @endphp
                    <div class="progress-bar bg-success" style="width: {{ $percentage }}%"></div>
                    <div class="progress-bar bg-info" style="width: {{ (($uploadedDocs->count() - $verifiedCount) / count($requiredDocs)) * 100 }}%"></div>
                </div>
                <small class="text-muted mt-1 d-block">
                    <i class="fas fa-info-circle mr-1"></i>
                    {{ $verifiedCount }} dokumen terverifikasi
                </small>
            </div>
        </div>

        @if($uploadedDocs->where('status_verifikasi', 'revision')->count() > 0)
        <div class="alert alert-warning" style="border-color: #f59e0b; background-color: #fffbeb;">
            <h5 class="alert-heading">
                <i class="fas fa-exclamation-triangle"></i>
                Perhatian!
            </h5>
            <p class="mb-2">
                Ada <strong>{{ $uploadedDocs->where('status_verifikasi', 'revision')->count() }}</strong> dokumen yang perlu diperbaiki.
            </p>
            <hr style="border-color: #fbbf24;">
            <p class="mb-0 small">
                <i class="fas fa-lightbulb mr-1"></i>
                Silakan klik pada kartu dokumen untuk melihat catatan revisi dari admin dan upload ulang dokumen yang sudah diperbaiki.
            </p>
        </div>
        @endif

        @if($calonSiswa->data_dokumen_completed && $uploadedDocs->where('status_verifikasi', 'valid')->count() === count($requiredDocs))
        <div class="alert alert-success">
            <h5 class="alert-heading">
                <i class="fas fa-check-circle"></i>
                Dokumen Lengkap!
            </h5>
            <p class="mb-0">
                Semua dokumen Anda telah diverifikasi dan disetujui. Anda dapat melanjutkan ke tahap selanjutnya.
            </p>
        </div>
        @endif
    </div>
</div>

{{-- Modal Upload Dokumen Tambahan --}}
@if($izinkanDokumenTambahan && !$calonSiswa->is_finalisasi)
<div class="modal fade" id="uploadDokumenTambahanModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title text-white">
                    <i class="fas fa-plus-circle mr-2"></i>Upload Dokumen Tambahan
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formDokumenTambahan" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="jenis_dokumen_tambahan"><i class="fas fa-tag mr-1"></i> Jenis Dokumen <span class="text-danger">*</span></label>
                        <select class="form-control" id="jenis_dokumen_tambahan" name="jenis_dokumen" required>
                            <option value="">-- Pilih Jenis Dokumen --</option>
                            @foreach($dokumenTambahanOptions as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="keterangan_dokumen"><i class="fas fa-edit mr-1"></i> Keterangan (Opsional)</label>
                        <input type="text" class="form-control" id="keterangan_dokumen" name="keterangan" 
                               placeholder="Contoh: Juara 1 Olimpiade Matematika 2025" maxlength="255">
                        <small class="text-muted">Tambahkan keterangan untuk memudahkan identifikasi dokumen</small>
                    </div>
                    
                    {{-- Pilih Sumber File --}}
                    <div class="form-group">
                        <label><i class="fas fa-image mr-1"></i> Sumber File <span class="text-danger">*</span></label>
                        <div class="btn-group btn-group-toggle d-flex" data-toggle="buttons">
                            <label class="btn btn-outline-success active flex-fill">
                                <input type="radio" name="file_source" id="sourceFile" value="file" checked> 
                                <i class="fas fa-folder-open mr-1"></i> Pilih File
                            </label>
                            <label class="btn btn-outline-primary flex-fill">
                                <input type="radio" name="file_source" id="sourceCamera" value="camera"> 
                                <i class="fas fa-camera mr-1"></i> Kamera
                            </label>
                        </div>
                    </div>
                    
                    {{-- File Input --}}
                    <div class="form-group" id="fileInputGroup">
                        <label for="file_dokumen_tambahan"><i class="fas fa-file mr-1"></i> Pilih File</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="file_dokumen_tambahan" name="file" 
                                   accept=".pdf,.jpg,.jpeg,.png">
                            <label class="custom-file-label" for="file_dokumen_tambahan">Pilih file...</label>
                        </div>
                        <small class="text-muted">Format: PDF, JPG, JPEG, PNG. Maksimal 5MB</small>
                    </div>
                    
                    {{-- Camera Input --}}
                    <div class="form-group" id="cameraInputGroup" style="display: none;">
                        <div class="text-center">
                            <video id="cameraVideoTambahan" autoplay playsinline style="width: 100%; max-width: 400px; border-radius: 8px; border: 2px solid #ddd; display: none;"></video>
                            <canvas id="cameraCanvasTambahan" style="display: none;"></canvas>
                        </div>
                        <div class="text-center mt-2" id="cameraControlsTambahan" style="display: none;">
                            <button type="button" class="btn btn-primary btn-lg" id="btnCaptureTambahan">
                                <i class="fas fa-camera"></i> Ambil Foto
                            </button>
                            <button type="button" class="btn btn-secondary btn-lg" id="btnRetakeTambahan" style="display: none;">
                                <i class="fas fa-redo"></i> Ulangi
                            </button>
                        </div>
                        <div class="text-center mt-2" id="cameraStartBtn">
                            <button type="button" class="btn btn-primary" id="btnStartCameraTambahan">
                                <i class="fas fa-video"></i> Mulai Kamera
                            </button>
                        </div>
                    </div>
                    
                    {{-- Preview --}}
                    <div id="previewDokumenTambahan" class="text-center mb-3" style="display: none;">
                        <p class="text-muted mb-2"><small>Preview:</small></p>
                        <img id="previewImgTambahan" src="" style="max-width: 300px; max-height: 200px; border-radius: 8px; border: 1px solid #ddd;">
                    </div>
                    
                    {{-- Hidden input for camera captured image --}}
                    <input type="hidden" id="camera_captured_tambahan" name="camera_captured">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Batal
                </button>
                <button type="button" class="btn btn-success" id="btnUploadDokumenTambahan">
                    <i class="fas fa-upload mr-1"></i>Upload
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Modal Preview Dokumen Tambahan --}}
<div class="modal fade" id="previewDokumenTambahanModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title text-white" id="previewDokTambahanTitle">
                    <i class="fas fa-eye mr-2"></i>Preview Dokumen
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body text-center" style="min-height: 300px;">
                <img id="previewDokTambahanImage" src="" style="max-width: 100%; max-height: 500px; display: none; border-radius: 8px;">
                <iframe id="previewDokTambahanPdf" src="" style="width: 100%; height: 500px; display: none; border: none;"></iframe>
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-primary" id="downloadDokTambahan" target="_blank">
                    <i class="fas fa-download mr-1"></i>Download
                </a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-upload mr-2"></i>
                    <span id="modalDocLabel">Upload Dokumen</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Preview for existing document -->
                <div id="existingDoc" style="display: none;">
                    <div class="text-center mb-3">
                        <img id="previewImage" src="" class="preview-img" style="display: none;">
                        <div id="previewPdf" style="display: none;">
                            <i class="fas fa-file-pdf fa-5x text-danger"></i>
                            <p class="mt-2" id="pdfFileName"></p>
                        </div>
                    </div>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="#" id="viewDocBtn" class="btn btn-info btn-sm" target="_blank">
                            <i class="fas fa-eye"></i> Lihat
                        </a>
                        @if(!$calonSiswa->is_finalisasi)
                        <button type="button" id="deleteDocBtn" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                        @endif
                    </div>
                    @if(!$calonSiswa->is_finalisasi)
                    <hr>
                    <p class="text-center text-muted mb-3">Atau upload file baru untuk mengganti</p>
                    @endif
                </div>

                <!-- Upload form -->
                @if(!$calonSiswa->is_finalisasi)
                <form id="uploadForm" enctype="multipart/form-data">
                    <input type="hidden" name="jenis_dokumen" id="jenisDoc">
                    <input type="hidden" name="doc_id" id="docId">
                    
                    <div class="form-group">
                        <label>Pilih File</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="fileInput" name="file" 
                                   accept=".pdf,.jpg,.jpeg,.png">
                            <label class="custom-file-label" for="fileInput">Pilih file...</label>
                        </div>
                        <small class="text-muted">Format: PDF, JPG, JPEG, PNG. Maks: 2MB</small>
                    </div>

                    <div id="uploadPreview" class="text-center" style="display: none;">
                        <img id="newPreviewImage" src="" class="preview-img">
                    </div>
                </form>
                @else
                <div class="alert alert-info text-center mb-0">
                    <i class="fas fa-info-circle"></i> Dokumen tidak dapat diubah karena data sudah difinalisasi
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ $calonSiswa->is_finalisasi ? 'Tutup' : 'Batal' }}</button>
                @if(!$calonSiswa->is_finalisasi)
                <button type="button" class="btn btn-primary" id="uploadBtn" disabled>
                    <i class="fas fa-upload mr-1"></i> Upload
                </button>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    let currentDocType = '';
    let currentDocId = '';

    // Open modal
    $('#uploadModal').on('show.bs.modal', function(e) {
        const button = $(e.relatedTarget);
        currentDocType = button.data('doc-type');
        currentDocId = button.data('doc-id') || '';
        
        $('#modalDocLabel').text('Upload ' + button.data('doc-label'));
        $('#jenisDoc').val(currentDocType);
        $('#docId').val(currentDocId);
        
        // Reset form
        $('#uploadForm')[0].reset();
        $('.custom-file-label').text('Pilih file...');
        $('#uploadPreview').hide();
        $('#uploadBtn').prop('disabled', true);
        
        // Show existing doc if uploaded
        if (currentDocId) {
            const docPath = button.data('doc-path');
            const docName = button.data('doc-name');
            
            $('#existingDoc').show();
            $('#viewDocBtn').attr('href', docPath);
            
            if (docName.match(/\.(jpg|jpeg|png)$/i)) {
                $('#previewImage').attr('src', docPath).show();
                $('#previewPdf').hide();
            } else {
                $('#previewImage').hide();
                $('#previewPdf').show();
                $('#pdfFileName').text(docName);
            }
        } else {
            $('#existingDoc').hide();
        }
    });

    // File input change
    $('#fileInput').on('change', function() {
        const file = this.files[0];
        if (file) {
            // Validate file size
            if (file.size > 2 * 1024 * 1024) {
                toastr.error('Ukuran file maksimal 2MB');
                this.value = '';
                return;
            }
            
            $('.custom-file-label').text(file.name);
            $('#uploadBtn').prop('disabled', false);
            
            // Show preview for images
            if (file.type.match(/image\/(jpg|jpeg|png)/i)) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#newPreviewImage').attr('src', e.target.result);
                    $('#uploadPreview').show();
                };
                reader.readAsDataURL(file);
            } else {
                $('#uploadPreview').hide();
            }
        }
    });

    // Upload button
    $('#uploadBtn').on('click', function() {
        const formData = new FormData($('#uploadForm')[0]);
        const btn = $(this);
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Uploading...');
        
        $.ajax({
            url: '{{ route("pendaftar.dokumen.upload") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#uploadModal').modal('hide');
                    location.reload();
                } else {
                    toastr.error(response.message || 'Gagal upload');
                }
            },
            error: function(xhr) {
                let message = 'Terjadi kesalahan';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                toastr.error(message);
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-upload mr-1"></i> Upload');
            }
        });
    });

    // Delete document
    $('#deleteDocBtn').on('click', function() {
        if (!confirm('Yakin ingin menghapus dokumen ini?')) return;
        
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        $.ajax({
            url: '{{ url("pendaftar/dokumen") }}/' + currentDocId,
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#uploadModal').modal('hide');
                    location.reload();
                } else {
                    toastr.error(response.message || 'Gagal menghapus');
                }
            },
            error: function(xhr) {
                toastr.error('Terjadi kesalahan');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-trash"></i> Hapus');
            }
        });
    });
    
    // ==========================================
    // DOKUMEN TAMBAHAN
    // ==========================================
    
    let cameraStreamTambahan = null;
    let capturedImageTambahan = null;
    
    // Toggle file/camera source
    $('input[name="file_source"]').on('change', function() {
        const source = $(this).val();
        if (source === 'file') {
            $('#fileInputGroup').show();
            $('#cameraInputGroup').hide();
            stopCameraTambahan();
        } else {
            $('#fileInputGroup').hide();
            $('#cameraInputGroup').show();
            $('#cameraStartBtn').show();
        }
        // Reset preview
        $('#previewDokumenTambahan').hide();
        capturedImageTambahan = null;
        $('#camera_captured_tambahan').val('');
    });
    
    // Start camera
    $('#btnStartCameraTambahan').on('click', function() {
        startCameraTambahan();
    });
    
    function startCameraTambahan() {
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ 
                video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } } 
            })
            .then(function(stream) {
                cameraStreamTambahan = stream;
                const video = document.getElementById('cameraVideoTambahan');
                video.srcObject = stream;
                video.style.display = 'block';
                $('#cameraControlsTambahan').show();
                $('#cameraStartBtn').hide();
                $('#btnCaptureTambahan').show();
                $('#btnRetakeTambahan').hide();
            })
            .catch(function(err) {
                console.error('Camera error:', err);
                toastr.error('Tidak dapat mengakses kamera. Pastikan browser diizinkan mengakses kamera.');
            });
        } else {
            toastr.error('Browser tidak mendukung akses kamera');
        }
    }
    
    function stopCameraTambahan() {
        if (cameraStreamTambahan) {
            cameraStreamTambahan.getTracks().forEach(track => track.stop());
            cameraStreamTambahan = null;
        }
        $('#cameraVideoTambahan').hide();
        $('#cameraControlsTambahan').hide();
    }
    
    // Capture photo
    $('#btnCaptureTambahan').on('click', function() {
        const video = document.getElementById('cameraVideoTambahan');
        const canvas = document.getElementById('cameraCanvasTambahan');
        const ctx = canvas.getContext('2d');
        
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        ctx.drawImage(video, 0, 0);
        
        capturedImageTambahan = canvas.toDataURL('image/jpeg', 0.8);
        $('#camera_captured_tambahan').val(capturedImageTambahan);
        
        // Show preview
        $('#previewImgTambahan').attr('src', capturedImageTambahan);
        $('#previewDokumenTambahan').show();
        
        // Hide video, show retake
        $('#cameraVideoTambahan').hide();
        $('#btnCaptureTambahan').hide();
        $('#btnRetakeTambahan').show();
        
        stopCameraTambahan();
    });
    
    // Retake photo
    $('#btnRetakeTambahan').on('click', function() {
        capturedImageTambahan = null;
        $('#camera_captured_tambahan').val('');
        $('#previewDokumenTambahan').hide();
        startCameraTambahan();
    });
    
    // Preview file dokumen tambahan
    $('#file_dokumen_tambahan').on('change', function() {
        const file = this.files[0];
        if (file) {
            // Validate size (5MB max)
            if (file.size > 5 * 1024 * 1024) {
                toastr.error('Ukuran file maksimal 5MB');
                this.value = '';
                $(this).next('.custom-file-label').text('Pilih file...');
                return;
            }
            
            $(this).next('.custom-file-label').text(file.name);
            
            // Show preview for images
            if (file.type.match(/image\/(jpg|jpeg|png)/i)) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#previewImgTambahan').attr('src', e.target.result);
                    $('#previewDokumenTambahan').show();
                };
                reader.readAsDataURL(file);
            } else {
                $('#previewDokumenTambahan').hide();
            }
        }
    });
    
    // Reset modal dokumen tambahan
    $('#uploadDokumenTambahanModal').on('hidden.bs.modal', function() {
        $('#formDokumenTambahan')[0].reset();
        $('#file_dokumen_tambahan').next('.custom-file-label').text('Pilih file...');
        $('#previewDokumenTambahan').hide();
        stopCameraTambahan();
        capturedImageTambahan = null;
        $('#camera_captured_tambahan').val('');
        // Reset to file source
        $('input[name="file_source"][value="file"]').prop('checked', true).parent().addClass('active');
        $('input[name="file_source"][value="camera"]').parent().removeClass('active');
        $('#fileInputGroup').show();
        $('#cameraInputGroup').hide();
        $('#cameraStartBtn').show();
    });
    
    // Upload dokumen tambahan
    $('#btnUploadDokumenTambahan').on('click', function() {
        const jenisDokumen = $('#jenis_dokumen_tambahan').val();
        const fileSource = $('input[name="file_source"]:checked').val();
        const fileInput = $('#file_dokumen_tambahan')[0];
        
        // Validate required fields
        if (!jenisDokumen) {
            toastr.error('Pilih jenis dokumen terlebih dahulu');
            return;
        }
        
        // Validate file or camera capture
        if (fileSource === 'file' && !fileInput.files.length) {
            toastr.error('Pilih file untuk diupload');
            return;
        }
        
        if (fileSource === 'camera' && !capturedImageTambahan) {
            toastr.error('Ambil foto terlebih dahulu');
            return;
        }
        
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('jenis_dokumen', jenisDokumen);
        formData.append('keterangan', $('#keterangan_dokumen').val());
        
        if (fileSource === 'file') {
            formData.append('file', fileInput.files[0]);
        } else {
            // Convert base64 to blob
            const blob = dataURLtoBlob(capturedImageTambahan);
            formData.append('file', blob, 'camera_capture_' + Date.now() + '.jpg');
        }
        
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Uploading...');
        
        $.ajax({
            url: '{{ route("pendaftar.dokumen-tambahan.upload") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#uploadDokumenTambahanModal').modal('hide');
                    location.reload();
                } else {
                    toastr.error(response.message || 'Gagal upload dokumen');
                }
            },
            error: function(xhr) {
                let message = 'Terjadi kesalahan';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors);
                        message = errors[0][0] || message;
                    } else if (xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                }
                toastr.error(message);
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-upload mr-1"></i>Upload');
            }
        });
    });
    
    // Helper: Convert dataURL to Blob
    function dataURLtoBlob(dataURL) {
        const parts = dataURL.split(',');
        const mime = parts[0].match(/:(.*?);/)[1];
        const bstr = atob(parts[1]);
        let n = bstr.length;
        const u8arr = new Uint8Array(n);
        while (n--) {
            u8arr[n] = bstr.charCodeAt(n);
        }
        return new Blob([u8arr], { type: mime });
    }
    
    // Preview dokumen tambahan modal
    $(document).on('click', '.btn-preview-dokumen-tambahan', function() {
        const url = $(this).data('url');
        const title = $(this).data('title');
        const type = $(this).data('type');
        
        $('#previewDokTambahanTitle').html('<i class="fas fa-eye mr-2"></i>' + title);
        $('#downloadDokTambahan').attr('href', url);
        
        if (type === 'image') {
            $('#previewDokTambahanImage').attr('src', url).show();
            $('#previewDokTambahanPdf').hide();
        } else {
            $('#previewDokTambahanImage').hide();
            $('#previewDokTambahanPdf').attr('src', url).show();
        }
        
        $('#previewDokumenTambahanModal').modal('show');
    });
    
    // Delete dokumen tambahan
    $(document).on('click', '.btn-delete-dokumen-tambahan', function() {
        const id = $(this).data('id');
        const nama = $(this).data('nama');
        
        if (!confirm('Yakin ingin menghapus dokumen "' + nama + '"?')) return;
        
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        $.ajax({
            url: '{{ url("pendaftar/dokumen-tambahan") }}/' + id,
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    location.reload();
                } else {
                    toastr.error(response.message || 'Gagal menghapus dokumen');
                }
            },
            error: function(xhr) {
                let message = 'Terjadi kesalahan';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                toastr.error(message);
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-trash"></i>');
            }
        });
    });
});
</script>
@endsection
