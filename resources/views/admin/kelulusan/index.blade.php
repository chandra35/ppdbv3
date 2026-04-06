@extends('adminlte::page')

@section('title', 'Penetapan Kelulusan')

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .nilai-cell { font-weight: bold; }
    .rank-badge {
        width: 30px; height: 30px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center; font-weight: bold;
    }
    .rank-1 { background: gold; color: #000; }
    .rank-2 { background: silver; color: #000; }
    .rank-3 { background: #cd7f32; color: #fff; }
    .status-lulus { background: #28a745; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem; }
    .status-tidak_lulus { background: #dc3545; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem; }
    .status-cadangan { background: #ffc107; color: #000; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem; }
    .check-col { width: 35px; text-align: center; }
    .selected-row { background-color: #d4edda !important; }
    .btn-luluskan { 
        position: sticky; bottom: 0; z-index: 10; 
        background: linear-gradient(135deg, #28a745, #20c997);
        border: none; font-size: 1.1rem;
    }
    /* Floating action bar */
    .floating-action-bar {
        position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%);
        z-index: 1050; display: none;
        background: #fff; border-radius: 50px; padding: 12px 24px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.25);
        animation: slideUp 0.3s ease-out;
    }
    @keyframes slideUp {
        from { transform: translateX(-50%) translateY(100px); opacity: 0; }
        to { transform: translateX(-50%) translateY(0); opacity: 1; }
    }
    .floating-action-bar .selected-count {
        font-weight: 700; font-size: 1.1rem; color: #007bff;
    }
    /* Custom confirmation modal */
    .kelulusan-modal-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .kelulusan-lulus-gradient {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }
    .confetti-container { position: relative; overflow: hidden; }
    .filter-label {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #5c6b7a;
    }
    .filter-select {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        width: 100%;
        min-height: calc(2.25rem + 2px);
        padding: 0.5rem 2.5rem 0.5rem 0.85rem;
        border: 1px solid #cfd7df;
        border-radius: 0.6rem;
        background-color: #fff;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 20 20'%3E%3Cpath fill='%235c6b7a' d='M5.5 7.5L10 12l4.5-4.5' stroke='%235c6b7a' stroke-width='2' fill='none' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.8rem center;
        background-size: 18px 18px;
        color: #243b53;
        font-size: 0.92rem;
        font-weight: 500;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        transition: border-color .15s ease, box-shadow .15s ease, background-color .15s ease;
    }
    .filter-select:hover {
        border-color: #b8c4d1;
    }
    .filter-select:focus {
        outline: none;
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.12);
    }
    .filter-select:disabled {
        background-color: #f8fafc;
        color: #9aa5b1;
        cursor: not-allowed;
    }
    .filter-hint {
        font-size: 0.76rem;
        color: #8898aa;
        margin-top: 0.35rem;
    }
</style>
@stop

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-graduation-cap mr-2"></i>Penetapan Kelulusan</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Kelulusan</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="alert alert-info">
        Kelulusan sedang memakai konteks:
        Tahun <strong>{{ $contextInfo['tahun'] }}</strong>,
        Jalur <strong>{{ $contextInfo['jalur'] }}</strong>,
        Gelombang <strong>{{ $contextInfo['gelombang'] }}</strong>.
    </div>

    <div class="card card-outline card-info">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="text-muted text-uppercase small font-weight-bold">Status Pengumuman Scope Aktif</div>
                    <div class="d-flex align-items-center flex-wrap mt-1">
                        @if($setting && $setting->isPengumumanAktif())
                            <span class="badge badge-success px-3 py-2 mr-2 mb-2"><i class="fas fa-broadcast-tower mr-1"></i>Sudah Dipublish</span>
                        @elseif($setting && $setting->tampilkan_pengumuman && $setting->tanggal_pengumuman && now()->lt($setting->tanggal_pengumuman))
                            <span class="badge badge-warning px-3 py-2 mr-2 mb-2"><i class="fas fa-clock mr-1"></i>Terjadwal {{ $setting->tanggal_pengumuman->format('d/m/Y H:i') }} WIB</span>
                        @else
                            <span class="badge badge-secondary px-3 py-2 mr-2 mb-2"><i class="fas fa-eye-slash mr-1"></i>Belum Dipublish</span>
                        @endif

                        @if($setting)
                            <small class="text-muted mb-2">{{ $setting->scope_description }}</small>
                        @endif
                    </div>
                </div>
                <div class="col-md-4 text-md-right mt-3 mt-md-0">
                    <a href="{{ route('admin.kelulusan.setting', ['tahun_pelajaran_id' => $selectedTahunIdInput, 'jalur_id' => $selectedJalurIdInput, 'gelombang_id' => $selectedGelombangIdInput]) }}" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-bullhorn mr-1"></i>Atur Pengumuman Scope Ini
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Total Peserta</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['total_lulus'] }}</h3>
                    <p>Dinyatakan Lulus</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['total_tidak_lulus'] }}</h3>
                    <p>Tidak Lulus</p>
                </div>
                <div class="icon"><i class="fas fa-times-circle"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['total_cadangan'] }}</h3>
                    <p>Cadangan</p>
                </div>
                <div class="icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>
    </div>

    <!-- Filter & NISN Search -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter mr-2"></i>Filter & Pencarian NISN</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.kelulusan.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label class="mb-1 filter-label">Tahun Pelajaran</label>
                        <select name="tahun_pelajaran_id" id="tahun_pelajaran_id" class="filter-select">
                            @foreach($tahunPelajarans as $tp)
                                <option value="{{ $tp->id }}" {{ $selectedTahunIdInput == $tp->id ? 'selected' : '' }}>{{ $tp->nama }}</option>
                            @endforeach
                        </select>
                        <div class="filter-hint">Tahun aktif menjadi basis daftar jalur dan gelombang.</div>
                    </div>
                    <div class="col-md-2">
                        <label class="mb-1 filter-label">Jalur</label>
                        <select name="jalur_id" id="jalur_id" class="filter-select">
                            <option value="all" {{ $selectedJalurIdInput === 'all' ? 'selected' : '' }}>Semua Jalur</option>
                            @foreach($jalurs as $jalur)
                                <option value="{{ $jalur->id }}" {{ (string) $selectedJalurIdInput === (string) $jalur->id ? 'selected' : '' }}>{{ $jalur->nama }}</option>
                            @endforeach
                        </select>
                        <div class="filter-hint">Memilih jalur akan langsung menyesuaikan pilihan gelombang.</div>
                    </div>
                    <div class="col-md-2">
                        <label class="mb-1 filter-label">Gelombang</label>
                        <select name="gelombang_id" id="gelombang_id" class="filter-select">
                            <option value="all" {{ $selectedGelombangIdInput === 'all' ? 'selected' : '' }}>Semua Gelombang</option>
                            @foreach($allGelombangs as $gel)
                                <option
                                    value="{{ $gel->id }}"
                                    data-jalur-id="{{ $gel->jalur_id }}"
                                    {{ (string) $selectedGelombangIdInput === (string) $gel->id ? 'selected' : '' }}
                                >{{ $gel->nama }}</option>
                            @endforeach
                        </select>
                        <div class="filter-hint">Hanya gelombang milik jalur terpilih yang ditampilkan.</div>
                    </div>
                    <div class="col-md-3">
                        <label class="mb-1 filter-label">Cari NISN <small class="text-muted">(pisahkan dengan Enter)</small></label>
                        <textarea name="nisn_search" class="form-control form-control-sm" rows="2" placeholder="0012345678&#10;0012345679">{{ $nisnSearch }}</textarea>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm mr-1"><i class="fas fa-search mr-1"></i>Filter</button>
                        <a href="{{ route('admin.kelulusan.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-sync mr-1"></i>Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Keterangan Bobot -->
    <div class="callout callout-info py-2">
        <p class="mb-0">
            <i class="fas fa-info-circle mr-1"></i>
            <strong>Nilai Akhir</strong> = CBT×50% + Rapor×10% + TBQ×40% | 
            Centang siswa yang akan diluluskan, kemudian klik tombol <strong>Luluskan</strong>.
        </p>
    </div>

    <!-- Data Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title"><i class="fas fa-table mr-2"></i>Data Peserta Seleksi</h3>
            <div>
                <a href="{{ route('admin.kelulusan.setting', ['tahun_pelajaran_id' => $selectedTahunIdInput, 'jalur_id' => $selectedJalurIdInput, 'gelombang_id' => $selectedGelombangIdInput]) }}" class="btn btn-outline-info btn-sm">
                    <i class="fas fa-cog mr-1"></i>Pengaturan Kelulusan
                </a>
            </div>
        </div>
        <div class="card-body">
            <div style="overflow-x: auto;">
                <table id="kelulusanTable" class="table table-bordered table-striped table-hover" style="font-size: 0.8rem;">
                    <thead>
                        <tr>
                            <th class="check-col">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="selectAll">
                                    <label class="custom-control-label" for="selectAll"></label>
                                </div>
                            </th>
                            <th class="text-center" width="40">Rank</th>
                            <th>No. Tes</th>
                            <th>NISN</th>
                            <th>Nama Peserta</th>
                            <th>JK</th>
                            <th>Jalur</th>
                            <th>Gelombang</th>
                            <th title="Pilihan Program">Pilihan</th>
                            <th class="text-center" style="background: #e8f5e9;">T. TBQ</th>
                            <th class="text-center" style="background: #e3f2fd;">Rata CBT</th>
                            <th class="text-center" style="background: #fff3e0;">Rapor</th>
                            <th class="text-center" style="background: #fce4ec;">Nilai Akhir</th>
                            <th class="text-center">Status Kelulusan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rekapData as $index => $nilai)
                            @php
                                $cbt = $cbtData[$nilai->calon_siswa_id] ?? null;
                                $statusKelulusan = $kelulusanData[$nilai->calon_siswa_id] ?? null;
                            @endphp
                            <tr data-calon-id="{{ $nilai->calon_siswa_id }}" class="{{ $statusKelulusan ? 'table-' . ($statusKelulusan == 'lulus' ? 'success' : ($statusKelulusan == 'cadangan' ? 'warning' : 'danger')) : '' }}">
                                <td class="check-col">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input row-check" 
                                               id="check-{{ $nilai->calon_siswa_id }}" 
                                               value="{{ $nilai->calon_siswa_id }}">
                                        <label class="custom-control-label" for="check-{{ $nilai->calon_siswa_id }}"></label>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($index < 3)
                                        <span class="rank-badge rank-{{ $index + 1 }}">{{ $index + 1 }}</span>
                                    @else
                                        {{ $index + 1 }}
                                    @endif
                                </td>
                                <td>{{ $nilai->calonSiswa->nomor_tes ?? '-' }}</td>
                                <td><code>{{ $nilai->calonSiswa->nisn ?? '-' }}</code></td>
                                <td><strong>{{ $nilai->calonSiswa->nama_lengkap ?? '-' }}</strong></td>
                                <td class="text-center">
                                    @if($nilai->calonSiswa->jenis_kelamin == 'L')
                                        <span class="text-primary"><i class="fas fa-mars"></i></span>
                                    @else
                                        <span class="text-danger"><i class="fas fa-venus"></i></span>
                                    @endif
                                </td>
                                <td>{{ $nilai->calonSiswa?->jalurPendaftaran?->nama ?? '-' }}</td>
                                <td>{{ $nilai->calonSiswa?->gelombangPendaftaran?->nama ?? '-' }}</td>
                                <td class="text-center">
                                    @if($nilai->calonSiswa?->pilihan_program === 'Asrama')
                                        <span class="badge" style="background:#6f42c1;color:#fff;">Asrama</span>
                                    @elseif($nilai->calonSiswa?->pilihan_program === 'Reguler')
                                        <span class="badge" style="background:#20c997;color:#fff;">Reguler</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                {{-- TBQ Total --}}
                                <td class="text-center">
                                    @if(($nilai->total_nilai ?? 0) > 0)
                                        <span class="badge badge-success">{{ number_format($nilai->total_nilai, 2) }}</span>
                                    @else - @endif
                                </td>
                                {{-- CBT Rata --}}
                                <td class="text-center">
                                    @if($cbt)
                                        <span class="badge badge-info">{{ number_format($cbt->rata_rata, 2) }}</span>
                                    @else - @endif
                                </td>
                                {{-- Rapor --}}
                                <td class="text-center">
                                    @if($nilai->nilai_rapor_rata !== null)
                                        <span class="badge badge-warning">{{ number_format($nilai->nilai_rapor_rata, 2) }}</span>
                                    @else - @endif
                                </td>
                                {{-- Nilai Akhir --}}
                                <td class="text-center">
                                    <span class="badge badge-danger" style="font-size: 0.95rem; font-weight: bold;">
                                        {{ number_format($nilai->nilai_akhir, 2) }}
                                    </span>
                                </td>
                                {{-- Status --}}
                                <td class="text-center">
                                    @if($statusKelulusan === 'lulus')
                                        <span class="status-lulus"><i class="fas fa-check mr-1"></i>LULUS</span>
                                    @elseif($statusKelulusan === 'tidak_lulus')
                                        <span class="status-tidak_lulus"><i class="fas fa-times mr-1"></i>Tidak Lulus</span>
                                    @elseif($statusKelulusan === 'cadangan')
                                        <span class="status-cadangan"><i class="fas fa-clock mr-1"></i>Cadangan</span>
                                    @else
                                        <span class="text-muted">Belum Ditetapkan</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Floating Action Bar -->
<div class="floating-action-bar" id="floatingBar">
    <div class="d-flex align-items-center">
        <span class="mr-3"><span class="selected-count" id="selectedCount">0</span> siswa dipilih</span>
        <button class="btn btn-success btn-sm mr-2" onclick="showKelulusanModal('lulus')">
            <i class="fas fa-check-circle mr-1"></i>Luluskan
        </button>
        <button class="btn btn-warning btn-sm mr-2" onclick="showKelulusanModal('cadangan')">
            <i class="fas fa-clock mr-1"></i>Cadangan
        </button>
        <button class="btn btn-danger btn-sm mr-2" onclick="showKelulusanModal('tidak_lulus')">
            <i class="fas fa-times-circle mr-1"></i>Tidak Lulus
        </button>
        <button class="btn btn-outline-secondary btn-sm" onclick="showBatalkanModal()">
            <i class="fas fa-undo mr-1"></i>Batalkan
        </button>
    </div>
</div>

@endsection

@section('js')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    const $jalur = $('#jalur_id');
    const $gelombang = $('#gelombang_id');
    const selectedGelombangId = @json((string) ($selectedGelombangIdInput ?? 'all'));
    const allGelombangOptions = $gelombang.find('option').clone();

    function refreshGelombangOptions(preferredValue = null) {
        const jalurId = String($jalur.val() || 'all');
        const currentValue = preferredValue ?? String($gelombang.val() || 'all');

        $gelombang.empty();

        allGelombangOptions.each(function() {
            const $option = $(this).clone();
            const optionValue = String($option.val() || '');
            const optionJalurId = String($option.data('jalur-id') || '');

            if (optionValue === 'all' || jalurId === 'all' || optionJalurId === jalurId) {
                $gelombang.append($option);
            }
        });

        const hasSpecificOptions = $gelombang.find('option').length > 1;
        $gelombang.prop('disabled', !hasSpecificOptions && jalurId !== 'all');

        const optionExists = $gelombang.find(`option[value="${currentValue}"]`).length > 0;
        $gelombang.val(optionExists ? currentValue : 'all');
    }

    $jalur.on('change', function() {
        refreshGelombangOptions('all');
    });

    refreshGelombangOptions(selectedGelombangId);

    // Persistent selection store
    var selectedIds = new Set();

    // DataTable
    var table = $('#kelulusanTable').DataTable({
        orderCellsTop: true,
        order: [[12, 'desc']],
        pageLength: 50,
        language: { url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json' },
        columnDefs: [
            { orderable: false, targets: 0 }
        ]
    });

    // Restore checkbox state after draw (page change, search, sort)
    table.on('draw', function() {
        // Restore individual checkboxes
        table.rows({ page: 'current' }).nodes().each(function(row) {
            var id = $(row).data('calon-id');
            var cb = $(row).find('.row-check');
            if (selectedIds.has(id)) {
                cb.prop('checked', true);
                $(row).addClass('selected-row');
            } else {
                cb.prop('checked', false);
                $(row).removeClass('selected-row');
            }
        });
        // Update select-all checkbox state
        updateSelectAllState();
        updateFloatingBar();
    });

    // Select All - all filtered rows across ALL pages
    $('#selectAll').on('change', function() {
        var checked = this.checked;
        table.rows({ search: 'applied' }).nodes().each(function(row) {
            var id = $(row).data('calon-id');
            var cb = $(row).find('.row-check');
            cb.prop('checked', checked);
            if (checked) {
                selectedIds.add(id);
                $(row).addClass('selected-row');
            } else {
                selectedIds.delete(id);
                $(row).removeClass('selected-row');
            }
        });
        updateFloatingBar();
    });

    // Single checkbox
    $(document).on('change', '.row-check', function() {
        var row = $(this).closest('tr');
        var id = row.data('calon-id');
        if (this.checked) {
            selectedIds.add(id);
            row.addClass('selected-row');
        } else {
            selectedIds.delete(id);
            row.removeClass('selected-row');
        }
        updateSelectAllState();
        updateFloatingBar();
    });

    // Check if all visible filtered rows are selected
    function updateSelectAllState() {
        var allChecked = true;
        var visibleCount = 0;
        table.rows({ search: 'applied', page: 'current' }).nodes().each(function(row) {
            visibleCount++;
            var id = $(row).data('calon-id');
            if (!selectedIds.has(id)) {
                allChecked = false;
            }
        });
        $('#selectAll').prop('checked', visibleCount > 0 && allChecked);
    }

    // Override getSelectedIds to use persistent store
    window.getSelectedIds = function() {
        return Array.from(selectedIds);
    };

    window.updateFloatingBar = function() {
        var count = selectedIds.size;
        $('#selectedCount').text(count);
        if (count > 0) {
            $('#floatingBar').fadeIn(300);
        } else {
            $('#floatingBar').fadeOut(200);
        }
    };
});

function showKelulusanModal(status) {
    var ids = getSelectedIds();
    if (ids.length === 0) {
        toastr.warning('Pilih minimal 1 siswa');
        return;
    }

    var statusLabel, statusIcon, statusColor, gradientClass;
    switch(status) {
        case 'lulus':
            statusLabel = 'LULUS'; statusIcon = 'fa-graduation-cap'; statusColor = '#28a745'; gradientClass = 'kelulusan-lulus-gradient';
            break;
        case 'tidak_lulus':
            statusLabel = 'TIDAK LULUS'; statusIcon = 'fa-times-circle'; statusColor = '#dc3545'; gradientClass = '';
            break;
        case 'cadangan':
            statusLabel = 'CADANGAN'; statusIcon = 'fa-clock'; statusColor = '#ffc107'; gradientClass = '';
            break;
    }

    Swal.fire({
        html: `
            <div class="text-center">
                <div style="width: 100px; height: 100px; margin: 0 auto 20px; border-radius: 50%; background: ${statusColor}; display: flex; align-items: center; justify-content: center;">
                    <i class="fas ${statusIcon} fa-3x text-white"></i>
                </div>
                <h3 style="font-weight: 700; margin-bottom: 10px;">Konfirmasi Kelulusan</h3>
                <p class="text-muted mb-3">Anda akan menetapkan <strong style="color: ${statusColor}; font-size: 1.3rem;">${ids.length} siswa</strong> sebagai:</p>
                <div style="background: ${statusColor}; color: #fff; padding: 12px 24px; border-radius: 30px; display: inline-block; font-size: 1.5rem; font-weight: 700; letter-spacing: 2px; margin-bottom: 20px; box-shadow: 0 4px 15px ${statusColor}40;">
                    ${statusLabel}
                </div>
                <div class="form-group text-left mt-3">
                    <label class="font-weight-bold"><i class="fas fa-comment mr-1"></i>Catatan (opsional):</label>
                    <textarea id="swal-catatan" class="form-control" rows="2" placeholder="Catatan kelulusan..."></textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonColor: statusColor,
        cancelButtonColor: '#6c757d',
        confirmButtonText: `<i class="fas fa-check mr-2"></i>Ya, Tetapkan ${statusLabel}`,
        cancelButtonText: '<i class="fas fa-times mr-2"></i>Batal',
        reverseButtons: true,
        width: '550px',
        customClass: { popup: 'animate__animated animate__fadeInUp' },
        preConfirm: () => {
            return { catatan: document.getElementById('swal-catatan').value };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            processKelulusan(ids, status, result.value.catatan);
        }
    });
}

function processKelulusan(ids, status, catatan) {
    Swal.fire({
        title: 'Memproses...',
        html: '<div class="d-flex align-items-center justify-content-center"><i class="fas fa-spinner fa-spin fa-2x mr-3"></i><span>Menetapkan kelulusan...</span></div>',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => { Swal.showLoading(); }
    });

    $.ajax({
        url: "{{ route('admin.kelulusan.luluskan') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            tahun_pelajaran_id: "{{ $selectedTahunIdInput }}",
            calon_siswa_ids: ids,
            status: status,
            catatan: catatan
        },
        success: function(response) {
            if (response.success) {
                var icon = status === 'lulus' ? 'success' : (status === 'cadangan' ? 'warning' : 'error');
                Swal.fire({
                    icon: icon,
                    title: status === 'lulus' ? 'Berhasil! 🎓' : 'Berhasil!',
                    html: `<div class="text-center">
                        <p style="font-size: 1.1rem;">${response.message}</p>
                        ${status === 'lulus' ? '<div style="font-size: 3rem; margin: 10px 0;">🎉🎊🎓</div>' : ''}
                    </div>`,
                    confirmButtonColor: '#28a745',
                    confirmButtonText: '<i class="fas fa-check mr-2"></i>OK'
                }).then(() => {
                    location.reload();
                });
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: xhr.responseJSON?.message || 'Terjadi kesalahan',
                confirmButtonColor: '#dc3545'
            });
        }
    });
}

function showBatalkanModal() {
    var ids = getSelectedIds();
    if (ids.length === 0) {
        toastr.warning('Pilih minimal 1 siswa');
        return;
    }

    Swal.fire({
        html: `
            <div class="text-center">
                <div style="width: 80px; height: 80px; margin: 0 auto 15px; border-radius: 50%; background: #6c757d; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-undo fa-2x text-white"></i>
                </div>
                <h4>Batalkan Penetapan Kelulusan?</h4>
                <p class="text-muted">Status kelulusan <strong>${ids.length} siswa</strong> akan dikembalikan ke <em>Belum Ditetapkan</em></p>
            </div>
        `,
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-undo mr-1"></i>Ya, Batalkan',
        cancelButtonText: 'Tidak',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('admin.kelulusan.batalkan') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    tahun_pelajaran_id: "{{ $selectedTahunIdInput }}",
                    calon_siswa_ids: ids
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: response.message, confirmButtonColor: '#28a745' })
                            .then(() => location.reload());
                    }
                },
                error: function(xhr) {
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: xhr.responseJSON?.message || 'Terjadi kesalahan' });
                }
            });
        }
    });
}
</script>
@stop
