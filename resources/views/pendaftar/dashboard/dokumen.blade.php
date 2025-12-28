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
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-upload mr-2"></i>
                    Upload Dokumen Persyaratan
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
                                 data-toggle="modal" 
                                 data-target="#uploadModal"
                                 data-doc-type="{{ $key }}"
                                 data-doc-label="{{ $label }}"
                                 @if($isUploaded)
                                 data-doc-id="{{ $doc->id }}"
                                 data-doc-path="{{ asset('storage/' . $doc->file_path) }}"
                                 data-doc-name="{{ $doc->nama_file }}"
                                 @endif>
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
                        <button type="button" id="deleteDocBtn" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </div>
                    <hr>
                    <p class="text-center text-muted mb-3">Atau upload file baru untuk mengganti</p>
                </div>

                <!-- Upload form -->
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
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="uploadBtn" disabled>
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
});
</script>
@endsection
