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
    }
    
    .doc-card:hover {
        border-color: #667eea;
        background: #f8f9ff;
    }
    
    .doc-card.uploaded {
        border-color: #48bb78;
        border-style: solid;
        background: #f0fff4;
    }
    
    .doc-card .icon {
        font-size: 2.5rem;
        color: #999;
        margin-bottom: 1rem;
    }
    
    .doc-card.uploaded .icon {
        color: #48bb78;
    }
    
    .doc-card h5 {
        margin-bottom: 0.5rem;
        font-size: 1rem;
    }
    
    .doc-card .status {
        font-size: 0.85rem;
    }
    
    .doc-card .status.pending {
        color: #ed8936;
    }
    
    .doc-card .status.valid {
        color: #48bb78;
    }
    
    .doc-card .status.invalid {
        color: #f56565;
    }
    
    .doc-card .status.revision {
        color: #4299e1;
    }
    
    .preview-img {
        max-width: 100%;
        max-height: 200px;
        object-fit: contain;
        border-radius: 5px;
        margin-top: 1rem;
    }
    
    .btn-delete-doc {
        position: absolute;
        top: 10px;
        right: 10px;
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
                        @endphp
                        <div class="col-md-4 col-6 mb-4">
                            <div class="doc-card {{ $isUploaded ? 'uploaded' : '' }}" 
                                 data-toggle="modal" 
                                 data-target="#uploadModal"
                                 data-doc-type="{{ $key }}"
                                 data-doc-label="{{ $label }}"
                                 @if($isUploaded)
                                 data-doc-id="{{ $doc->id }}"
                                 data-doc-path="{{ Storage::url($doc->path_file) }}"
                                 data-doc-name="{{ $doc->nama_file }}"
                                 @endif>
                                <div class="icon">
                                    @if($isUploaded)
                                        <i class="fas fa-file-check"></i>
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
                                            <i class="fas fa-check"></i> Terverifikasi
                                        @elseif($doc->status_verifikasi === 'invalid')
                                            <i class="fas fa-times"></i> Ditolak
                                        @elseif($doc->status_verifikasi === 'revision')
                                            <i class="fas fa-redo"></i> Perlu Revisi
                                        @else
                                            <i class="fas fa-clock"></i> {{ ucfirst($doc->status_verifikasi) }}
                                        @endif
                                    </span>
                                @else
                                    <span class="text-muted">Belum diupload</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-info">
                <h3 class="card-title text-white">
                    <i class="fas fa-clipboard-list mr-2"></i>
                    Checklist Dokumen
                </h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($requiredDocs as $key => $label)
                        @php $doc = $uploadedDocs->get($key); @endphp
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ $label }}
                            @if($doc)
                                @if($doc->status_verifikasi === 'valid')
                                    <span class="badge badge-success"><i class="fas fa-check"></i></span>
                                @elseif($doc->status_verifikasi === 'invalid')
                                    <span class="badge badge-danger"><i class="fas fa-times"></i></span>
                                @elseif($doc->status_verifikasi === 'revision')
                                    <span class="badge badge-info"><i class="fas fa-redo"></i></span>
                                @else
                                    <span class="badge badge-warning"><i class="fas fa-clock"></i></span>
                                @endif
                            @else
                                <span class="badge badge-secondary"><i class="fas fa-minus"></i></span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="card-footer">
                <small class="text-muted">
                    {{ $uploadedDocs->count() }} dari {{ count($requiredDocs) }} dokumen terupload
                </small>
                <div class="progress mt-2" style="height: 8px;">
                    <div class="progress-bar" style="width: {{ ($uploadedDocs->count() / count($requiredDocs)) * 100 }}%"></div>
                </div>
            </div>
        </div>

        @if($calonSiswa->data_dokumen_completed)
        <div class="alert alert-success">
            <i class="fas fa-check-circle mr-2"></i>
            <strong>Semua Dokumen Lengkap!</strong><br>
            Dokumen Anda sedang dalam proses verifikasi.
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
