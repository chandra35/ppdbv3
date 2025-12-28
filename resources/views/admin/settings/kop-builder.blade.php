@extends('adminlte::page')

@section('title', 'Kop Surat Builder')

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<style>
    .kop-builder-container {
        display: flex;
        gap: 20px;
        min-height: 600px;
    }
    
    .elements-panel {
        flex: 0 0 250px;
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .preview-panel {
        flex: 1;
        background: white;
        border: 2px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        overflow-y: auto;
        max-height: 800px;
    }
    
    .element-btn {
        width: 100%;
        margin-bottom: 10px;
        text-align: left;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 15px;
        border-radius: 6px;
        transition: all 0.3s;
    }
    
    .element-btn i {
        font-size: 18px;
        width: 25px;
    }
    
    .element-btn:hover {
        transform: translateX(5px);
    }
    
    #kopElementsList {
        min-height: 100px;
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .kop-element-item {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 12px 15px;
        margin-bottom: 10px;
        cursor: move;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.3s;
    }
    
    .kop-element-item:hover {
        background: #f8f9fa;
        border-color: #007bff;
        box-shadow: 0 2px 8px rgba(0,123,255,0.2);
    }
    
    .kop-element-item.sortable-ghost {
        opacity: 0.4;
        background: #e9ecef;
    }
    
    .kop-element-item.sortable-drag {
        opacity: 1;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .element-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .element-icon {
        font-size: 20px;
        width: 30px;
        text-align: center;
    }
    
    .element-actions {
        display: flex;
        gap: 5px;
    }
    
    .preview-kop {
        border: 1px solid #000;
        padding: 15px;
        min-height: 150px;
        background: white;
        font-family: Arial, sans-serif;
    }
    
    .preview-logo-row {
        display: table;
        width: 100%;
        margin-bottom: 10px;
    }
    
    .preview-logo-col {
        display: table-cell;
        vertical-align: top;
        padding: 5px;
    }
    
    .preview-logo-center {
        text-align: center;
    }
    
    .preview-logo-right {
        text-align: right;
    }
    
    .preview-text {
        text-align: center;
        margin: 5px 0;
    }
    
    .preview-divider {
        border: none;
        border-top: 1px solid #000;
        margin: 10px 0;
    }
    
    .upload-area {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        background: #f8f9fa;
        transition: all 0.3s;
        cursor: pointer;
    }
    
    .upload-area:hover {
        border-color: #007bff;
        background: #e7f3ff;
    }
    
    .upload-area.drag-over {
        border-color: #28a745;
        background: #e8f5e9;
    }
    
    .logo-preview {
        max-width: 150px;
        max-height: 100px;
        margin: 10px auto;
        display: block;
    }
    
    .badge-element {
        font-size: 10px;
        padding: 3px 8px;
    }
</style>
@stop

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-file-alt"></i> Kop Surat Builder</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}">Pengaturan PPDB</a></li>
                <li class="breadcrumb-item active">Kop Surat</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-paint-brush"></i> Desain Kop Surat
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-sm btn-primary" id="btnPreview">
                    <i class="fas fa-eye"></i> Preview
                </button>
                <button type="button" class="btn btn-sm btn-success" id="btnSave">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </div>
        <div class="card-body">
            {{-- Mode Selection --}}
            <div class="alert alert-info">
                <div class="form-group mb-0">
                    <label><strong>Mode Kop Surat:</strong></label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="kop_mode" id="modeBuilder" value="builder" {{ old('kop_mode', $sekolah->kop_mode ?? 'builder') == 'builder' ? 'checked' : '' }}>
                        <label class="form-check-label" for="modeBuilder">
                            <strong>Builder Mode</strong> - Drag & drop elemen untuk membuat kop surat
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="kop_mode" id="modeCustom" value="custom" {{ old('kop_mode', $sekolah->kop_mode ?? 'builder') == 'custom' ? 'checked' : '' }}>
                        <label class="form-check-label" for="modeCustom">
                            <strong>Custom Mode</strong> - Upload gambar kop surat sendiri (JPG/PNG)
                        </label>
                    </div>
                </div>
            </div>

            {{-- Builder Mode Panel --}}
            <div id="builderPanel" style="{{ old('kop_mode', $sekolah->kop_mode ?? 'builder') == 'builder' ? '' : 'display:none;' }}">
                <div class="kop-builder-container">
                    {{-- Left Panel: Available Elements --}}
                    <div class="elements-panel">
                        <h5 class="mb-3"><i class="fas fa-puzzle-piece"></i> Elemen Tersedia</h5>
                        
                        <button type="button" class="btn btn-outline-secondary element-btn" data-add="text">
                            <i class="fas fa-heading"></i>
                            <span>Teks/Heading</span>
                        </button>
                        
                        <button type="button" class="btn btn-outline-dark element-btn" data-add="divider">
                            <i class="fas fa-minus"></i>
                            <span>Garis Pembatas</span>
                        </button>
                        
                        <button type="button" class="btn btn-outline-info element-btn" data-add="contact">
                            <i class="fas fa-address-book"></i>
                            <span>Kontak Info</span>
                        </button>
                        
                        <hr>
                        
                        <div class="alert alert-info small mb-0">
                            <i class="fas fa-info-circle"></i> Logo Kemenag (kiri) dan Logo Sekolah (kanan) akan otomatis tampil di kop surat.
                        </div>
                        
                        <hr>
                        
                        <h6 class="mt-3 mb-2">Elemen Aktif: <span id="elementCount" class="badge badge-success">0</span></h6>
                        <small class="text-muted">Drag untuk mengatur urutan</small>
                    </div>
                    
                    {{-- Center Panel: Active Elements List --}}
                    <div class="flex-1">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-list"></i> Elemen Kop Surat</h5>
                            </div>
                            <div class="card-body">
                                <ul id="kopElementsList">
                                    @if($sekolah && $sekolah->kop_surat_config && isset($sekolah->kop_surat_config['elements']))
                                        @foreach($sekolah->kop_surat_config['elements'] as $index => $element)
                                            @php
                                                $icons = [
                                                    'text' => 'fas fa-heading',
                                                    'divider' => 'fas fa-minus',
                                                    'contact' => 'fas fa-address-book',
                                                ];
                                                $labels = [
                                                    'text' => 'Teks',
                                                    'divider' => 'Garis Pembatas',
                                                    'contact' => 'Kontak Info',
                                                ];
                                            @endphp
                                            <li class="kop-element-item" data-id="{{ $index }}" data-type="{{ $element['type'] }}">
                                                <div class="element-info">
                                                    <i class="element-icon {{ $icons[$element['type']] ?? 'fas fa-cube' }}"></i>
                                                    <div>
                                                        <strong>{{ $labels[$element['type']] ?? 'Unknown' }}</strong>
                                                        <span class="badge badge-element badge-secondary">{{ $element['type'] }}</span>
                                                    </div>
                                                </div>
                                                <div class="element-actions">
                                                    <button type="button" class="btn btn-sm btn-info btn-edit-element" data-index="{{ $index }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger btn-delete-element" data-index="{{ $index }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </li>
                                        @endforeach
                                    @endif
                                </ul>
                                
                                <div id="emptyState" style="{{ $sekolah && $sekolah->kop_surat_config && count($sekolah->kop_surat_config['elements'] ?? []) > 0 ? 'display:none;' : '' }}">
                                    <div class="text-center text-muted py-5">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>Belum ada elemen. Klik tombol di sebelah kiri untuk menambahkan.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Logo Upload Section --}}
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-primary">
                                <h5 class="mb-0 text-white"><i class="fas fa-image"></i> Upload Logo Kemenag</h5>
                            </div>
                            <div class="card-body">
                                <input type="file" id="fileLogoKemenag" accept="image/*" style="display:none;">
                                <div class="upload-area" id="uploadAreaKemenag">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-2"></i>
                                    <p class="mb-2">Klik atau drag file kesini</p>
                                    <small class="text-muted">Format: JPG, PNG (Max 2MB, 200x200px recommended)</small>
                                </div>
                                @if($sekolah && $sekolah->logo_kemenag_path)
                                    <div class="text-center mt-3">
                                        <img src="{{ asset('storage/' . $sekolah->logo_kemenag_path) }}" alt="Logo Kemenag" class="logo-preview">
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-danger" id="btnDeleteLogoKemenag">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-success">
                                <h5 class="mb-0 text-white"><i class="fas fa-school"></i> Logo Sekolah</h5>
                            </div>
                            <div class="card-body">
                                @if($sekolah && $sekolah->logo)
                                    <div class="text-center">
                                        <img src="{{ asset('storage/' . $sekolah->logo) }}" alt="Logo Sekolah" class="logo-preview">
                                        <p class="mt-2 text-muted">Logo sekolah diambil dari Pengaturan Sekolah</p>
                                        <a href="{{ route('admin.sekolah.index') }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-cog"></i> Ubah di Pengaturan
                                        </a>
                                    </div>
                                @else
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i> Logo sekolah belum diupload.
                                        <a href="{{ route('admin.sekolah.index') }}">Upload sekarang</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Custom Mode Panel --}}
            <div id="customPanel" style="{{ old('kop_mode', $sekolah->kop_mode ?? 'builder') == 'custom' ? '' : 'display:none;' }}">
                <div class="card">
                    <div class="card-body">
                        <input type="file" id="fileKopCustom" accept="image/*" style="display:none;">
                        <div class="upload-area" id="uploadAreaCustom">
                            <i class="fas fa-file-image fa-3x text-muted mb-2"></i>
                            <p class="mb-2">Upload gambar kop surat lengkap</p>
                            <small class="text-muted">Format: JPG, PNG (A4 size recommended, Max 5MB)</small>
                        </div>
                        @if($sekolah && $sekolah->kop_surat_custom_path)
                            <div class="text-center mt-3">
                                <img src="{{ asset('storage/' . $sekolah->kop_surat_custom_path) }}" alt="Kop Custom" style="max-width: 100%; border: 1px solid #dee2e6;">
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-danger" id="btnDeleteKopCustom">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Preview --}}
    <div class="modal fade" id="modalPreview" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-eye"></i> Preview Kop Surat</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="preview-kop" id="previewContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Edit Element --}}
    <div class="modal fade" id="modalEditElement" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Elemen</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="editElementForm">
                    <!-- Dynamic content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSaveElement">Simpan</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
$(document).ready(function() {
    let kopConfig = @json($sekolah->kop_surat_config ?? ['elements' => []]);
    let currentEditIndex = null;
    
    // Initialize Sortable
    const sortable = new Sortable(document.getElementById('kopElementsList'), {
        animation: 150,
        handle: '.kop-element-item',
        ghostClass: 'sortable-ghost',
        dragClass: 'sortable-drag',
        onEnd: function() {
            updateElementsOrder();
        }
    });
    
    // Mode toggle
    $('input[name="kop_mode"]').change(function() {
        if ($(this).val() === 'builder') {
            $('#builderPanel').show();
            $('#customPanel').hide();
        } else {
            $('#builderPanel').hide();
            $('#customPanel').show();
        }
    });
    
    // Add element buttons
    $('.element-btn[data-add]').click(function() {
        const type = $(this).data('add');
        addElement(type);
    });
    
    function addElement(type) {
        const element = {
            type: type,
            content: getDefaultContent(type)
        };
        
        kopConfig.elements.push(element);
        renderElementsList();
        updateElementCount();
        
        toastr.success('Elemen berhasil ditambahkan');
    }
    
    function getDefaultContent(type) {
        const defaults = {
            text: { 
                line1: 'KEMENTERIAN AGAMA REPUBLIK INDONESIA',
                line2: '{{ $sekolah->nama_sekolah ?? 'NAMA SEKOLAH' }}',
                line3: '',
                fontSize: 12,
                bold: true,
                align: 'center'
            },
            divider: {
                style: 'solid',
                width: 2,
                color: '#000000',
                marginTop: 5,
                marginBottom: 5
            },
            contact: {
                alamat: '{{ $sekolah->alamat_jalan ?? '' }}',
                telepon: '{{ $sekolah->telepon ?? '' }}',
                email: '{{ $sekolah->email ?? '' }}',
                website: '{{ $sekolah->website ?? '' }}'
            }
        };
        
        return defaults[type] || {};
    }
    
    function renderElementsList() {
        const $list = $('#kopElementsList');
        $list.empty();
        
        if (kopConfig.elements.length === 0) {
            $('#emptyState').show();
            return;
        }
        
        $('#emptyState').hide();
        
        const icons = {
            text: 'fas fa-heading',
            divider: 'fas fa-minus',
            contact: 'fas fa-address-book'
        };
        
        const labels = {
            text: 'Teks',
            divider: 'Garis Pembatas',
            contact: 'Kontak Info'
        };
        
        kopConfig.elements.forEach((element, index) => {
            const html = `
                <li class="kop-element-item" data-id="${index}" data-type="${element.type}">
                    <div class="element-info">
                        <i class="element-icon ${icons[element.type] || 'fas fa-cube'}"></i>
                        <div>
                            <strong>${labels[element.type] || 'Unknown'}</strong>
                            <span class="badge badge-element badge-secondary">${element.type}</span>
                        </div>
                    </div>
                    <div class="element-actions">
                        <button type="button" class="btn btn-sm btn-info btn-edit-element" data-index="${index}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-danger btn-delete-element" data-index="${index}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </li>
            `;
            $list.append(html);
        });
        
        // Re-attach event handlers
        $('.btn-edit-element').click(function() {
            editElement($(this).data('index'));
        });
        
        $('.btn-delete-element').click(function() {
            deleteElement($(this).data('index'));
        });
    }
    
    function updateElementsOrder() {
        const newOrder = [];
        $('#kopElementsList .kop-element-item').each(function() {
            const index = $(this).data('id');
            newOrder.push(kopConfig.elements[index]);
        });
        kopConfig.elements = newOrder;
        renderElementsList();
    }
    
    function editElement(index) {
        currentEditIndex = index;
        const element = kopConfig.elements[index];
        
        let formHtml = '';
        
        switch(element.type) {
            case 'text':
                formHtml = `
                    <div class="form-group">
                        <label>Baris 1</label>
                        <input type="text" class="form-control" id="editLine1" value="${element.content.line1 || ''}">
                    </div>
                    <div class="form-group">
                        <label>Baris 2</label>
                        <input type="text" class="form-control" id="editLine2" value="${element.content.line2 || ''}">
                    </div>
                    <div class="form-group">
                        <label>Baris 3</label>
                        <input type="text" class="form-control" id="editLine3" value="${element.content.line3 || ''}">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Ukuran Font</label>
                                <input type="number" class="form-control" id="editFontSize" value="${element.content.fontSize || 12}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Alignment</label>
                                <select class="form-control" id="editAlign">
                                    <option value="left" ${element.content.align === 'left' ? 'selected' : ''}>Kiri</option>
                                    <option value="center" ${element.content.align === 'center' ? 'selected' : ''}>Tengah</option>
                                    <option value="right" ${element.content.align === 'right' ? 'selected' : ''}>Kanan</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="editBold" ${element.content.bold ? 'checked' : ''}>
                        <label class="form-check-label" for="editBold">Bold</label>
                    </div>
                `;
                break;
                
            case 'divider':
                formHtml = `
                    <div class="form-group">
                        <label>Style</label>
                        <select class="form-control" id="editStyle">
                            <option value="solid" ${element.content.style === 'solid' ? 'selected' : ''}>Solid</option>
                            <option value="double" ${element.content.style === 'double' ? 'selected' : ''}>Double</option>
                            <option value="dashed" ${element.content.style === 'dashed' ? 'selected' : ''}>Dashed</option>
                            <option value="dotted" ${element.content.style === 'dotted' ? 'selected' : ''}>Dotted</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Width (px)</label>
                        <input type="number" class="form-control" id="editWidth" value="${element.content.width || 2}">
                    </div>
                    <div class="form-group">
                        <label>Color</label>
                        <input type="color" class="form-control" id="editColor" value="${element.content.color || '#000000'}">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Margin Top</label>
                                <input type="number" class="form-control" id="editMarginTop" value="${element.content.marginTop || 5}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Margin Bottom</label>
                                <input type="number" class="form-control" id="editMarginBottom" value="${element.content.marginBottom || 5}">
                            </div>
                        </div>
                    </div>
                `;
                break;
                
            case 'contact':
                formHtml = `
                    <div class="form-group">
                        <label>Alamat</label>
                        <input type="text" class="form-control" id="editAlamat" value="${element.content.alamat || ''}">
                    </div>
                    <div class="form-group">
                        <label>Telepon</label>
                        <input type="text" class="form-control" id="editTelepon" value="${element.content.telepon || ''}">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" id="editEmail" value="${element.content.email || ''}">
                    </div>
                    <div class="form-group">
                        <label>Website</label>
                        <input type="text" class="form-control" id="editWebsite" value="${element.content.website || ''}">
                    </div>
                `;
                break;
        }
        
        $('#editElementForm').html(formHtml);
        $('#modalEditElement').modal('show');
    }
    
    $('#btnSaveElement').click(function() {
        if (currentEditIndex === null) return;
        
        const element = kopConfig.elements[currentEditIndex];
        
        switch(element.type) {
            case 'text':
                element.content.line1 = $('#editLine1').val();
                element.content.line2 = $('#editLine2').val();
                element.content.line3 = $('#editLine3').val();
                element.content.fontSize = parseInt($('#editFontSize').val());
                element.content.align = $('#editAlign').val();
                element.content.bold = $('#editBold').is(':checked');
                break;
                
            case 'divider':
                element.content.style = $('#editStyle').val();
                element.content.width = parseInt($('#editWidth').val());
                element.content.color = $('#editColor').val();
                element.content.marginTop = parseInt($('#editMarginTop').val());
                element.content.marginBottom = parseInt($('#editMarginBottom').val());
                break;
                
            case 'contact':
                element.content.alamat = $('#editAlamat').val();
                element.content.telepon = $('#editTelepon').val();
                element.content.email = $('#editEmail').val();
                element.content.website = $('#editWebsite').val();
                break;
        }
        
        $('#modalEditElement').modal('hide');
        toastr.success('Elemen berhasil diupdate');
    });
    
    function deleteElement(index) {
        if (!confirm('Hapus elemen ini?')) return;
        
        kopConfig.elements.splice(index, 1);
        renderElementsList();
        updateElementCount();
        
        toastr.info('Elemen berhasil dihapus');
    }
    
    function updateElementCount() {
        $('#elementCount').text(kopConfig.elements.length);
    }
    
    // Preview
    $('#btnPreview').click(function() {
        generatePreview();
        $('#modalPreview').modal('show');
    });
    
    function generatePreview() {
        // Build center content (text elements only)
        let centerHtml = '';
        
        kopConfig.elements.forEach(element => {
            switch(element.type) {
                case 'text':
                    const style = `text-align:${element.content.align}; font-size:${element.content.fontSize}pt; ${element.content.bold ? 'font-weight:bold;' : ''}`;
                    if (element.content.line1) centerHtml += `<div style="${style}">${element.content.line1}</div>`;
                    if (element.content.line2) centerHtml += `<div style="${style}">${element.content.line2}</div>`;
                    if (element.content.line3) centerHtml += `<div style="${style}">${element.content.line3}</div>`;
                    break;
                    
                case 'divider':
                    centerHtml += `<hr style="border:none; border-top:${element.content.width}px ${element.content.style} ${element.content.color}; margin-top:${element.content.marginTop}px; margin-bottom:${element.content.marginBottom}px;">`;
                    break;
                    
                case 'contact':
                    centerHtml += '<div style="font-size:9pt; text-align:center;">';
                    if (element.content.alamat) centerHtml += `${element.content.alamat} `;
                    if (element.content.telepon) centerHtml += `| Telp: ${element.content.telepon} `;
                    if (element.content.email) centerHtml += `| Email: ${element.content.email} `;
                    if (element.content.website) centerHtml += `| ${element.content.website}`;
                    centerHtml += '</div>';
                    break;
            }
        });
        
        // Build 3-column layout: Logo Kemenag (left) | Text (center) | Logo Sekolah (right)
        const logoKemenagUrl = '{{ $sekolah && $sekolah->logo_kemenag_path ? asset("storage/" . $sekolah->logo_kemenag_path) : "" }}';
        const logoSekolahUrl = '{{ $sekolah && $sekolah->logo ? asset("storage/" . $sekolah->logo) : "" }}';
        
        const html = `
            <table width="100%" border="0" cellpadding="5" cellspacing="0">
                <tr>
                    <td width="15%" align="center" valign="top">
                        ${logoKemenagUrl ? `<img src="${logoKemenagUrl}" alt="Logo Kemenag" style="height:80px;">` : '<div style="color:#ccc;">Logo Kemenag</div>'}
                    </td>
                    <td width="70%" align="center" valign="top">
                        ${centerHtml || '<div style="color:#ccc;">Belum ada teks</div>'}
                    </td>
                    <td width="15%" align="center" valign="top">
                        ${logoSekolahUrl ? `<img src="${logoSekolahUrl}" alt="Logo Sekolah" style="height:80px;">` : '<div style="color:#ccc;">Logo Sekolah</div>'}
                    </td>
                </tr>
            </table>
        `;
        
        $('#previewContent').html(html);
    }
    
    // Save configuration
    $('#btnSave').click(function() {
        const mode = $('input[name="kop_mode"]:checked').val();
        
        $.ajax({
            url: '{{ route("admin.sekolah.kop-builder.update") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                kop_mode: mode,
                kop_surat_config: JSON.stringify(kopConfig)
            },
            success: function(response) {
                toastr.success(response.message || 'Konfigurasi berhasil disimpan');
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Gagal menyimpan konfigurasi');
            }
        });
    });
    
    // Upload handlers - Logo Kemenag
    $('#uploadAreaKemenag').click(function() {
        $('#fileLogoKemenag').click();
    });
    
    $('#fileLogoKemenag').change(function() {
        if (this.files && this.files[0]) {
            uploadLogo('kemenag', this.files[0]);
        }
    });
    
    // Upload handlers - Custom Kop
    $('#uploadAreaCustom').click(function() {
        $('#fileKopCustom').click();
    });
    
    $('#fileKopCustom').change(function() {
        if (this.files && this.files[0]) {
            uploadLogo('custom', this.files[0]);
        }
    });
    
    // Drag & Drop support
    $('.upload-area').on('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('drag-over');
    });
    
    $('.upload-area').on('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('drag-over');
    });
    
    $('#uploadAreaKemenag').on('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('drag-over');
        
        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            uploadLogo('kemenag', files[0]);
        }
    });
    
    $('#uploadAreaCustom').on('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('drag-over');
        
        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            uploadLogo('custom', files[0]);
        }
    });
    
    function uploadLogo(type, file) {
        if (!file) {
            toastr.error('File tidak valid');
            return;
        }
        
        // Validate file type
        const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!validTypes.includes(file.type)) {
            toastr.error('Format file harus JPG atau PNG');
            return;
        }
        
        // Validate file size (2MB for logo, 5MB for custom)
        const maxSize = (type === 'kemenag') ? 2 * 1024 * 1024 : 5 * 1024 * 1024;
        if (file.size > maxSize) {
            const maxMB = (type === 'kemenag') ? '2MB' : '5MB';
            toastr.error(`Ukuran file maksimal ${maxMB}`);
            return;
        }
        
        const formData = new FormData();
        formData.append('logo', file);
        formData.append('type', type);
        formData.append('_token', '{{ csrf_token() }}');
        
        // Show loading
        const loadingMsg = type === 'kemenag' ? 'Mengupload logo Kemenag...' : 'Mengupload kop custom...';
        toastr.info(loadingMsg);
        
        $.ajax({
            url: '{{ route("admin.sekolah.logo-kemenag.upload") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                toastr.success(response.message || 'Upload berhasil');
                setTimeout(function() {
                    location.reload();
                }, 1000);
            },
            error: function(xhr) {
                console.error('Upload error:', xhr);
                const errorMsg = xhr.responseJSON?.message || xhr.responseJSON?.errors?.logo?.[0] || 'Gagal upload file';
                toastr.error(errorMsg);
            }
        });
    }
    
    // Delete handlers
    $('#btnDeleteLogoKemenag').click(function() {
        if (!confirm('Hapus logo Kemenag?')) return;
        
        deleteAsset('logo_kemenag');
    });
    
    $('#btnDeleteKopCustom').click(function() {
        if (!confirm('Hapus kop surat custom?')) return;
        
        deleteAsset('kop_custom');
    });
    
    function deleteAsset(type) {
        $.ajax({
            url: '{{ route("admin.sekolah.kop-asset.delete") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                type: type
            },
            success: function(response) {
                toastr.success(response.message || 'Asset berhasil dihapus');
                setTimeout(function() {
                    location.reload();
                }, 1000);
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Gagal menghapus asset');
            }
        });
    }
    
    // Initialize
    updateElementCount();
});
</script>
@stop
