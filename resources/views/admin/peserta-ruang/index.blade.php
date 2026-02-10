@extends('adminlte::page')

@section('title', 'Kelola Peserta - ' . $ruangUjian->nama_ruang)

@section('css')
<style>
    .search-result-item {
        padding: 8px 12px;
        border-bottom: 1px solid #f0f0f0;
        cursor: pointer;
        transition: background 0.15s;
    }
    .search-result-item:hover { background: #e3f2fd; }
    .search-result-item:last-child { border-bottom: none; }
    .search-results-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid #dee2e6;
        border-top: none;
        border-radius: 0 0 8px 8px;
        max-height: 300px;
        overflow-y: auto;
        z-index: 100;
        display: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .peserta-row:hover { background: #f8f9fa; }
    .badge-room { font-size: 11px; padding: 4px 8px; }
</style>
@stop

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">
                <i class="fas fa-users-cog mr-2"></i>Kelola Peserta Ruangan
            </h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.sesi-ujian.show', $sesiUjian->id) }}">{{ $sesiUjian->nama }}</a></li>
                <li class="breadcrumb-item active">{{ $ruangUjian->nama_ruang }}</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle mr-1"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-circle mr-1"></i>{{ session('error') }}
        </div>
    @endif

    <div class="row">
        {{-- Info Ruangan --}}
        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-door-open mr-1"></i>{{ $ruangUjian->nama_ruang }}</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="text-muted">Sesi</td>
                            <td><strong>{{ $sesiUjian->nama }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tanggal</td>
                            <td>{{ $sesiUjian->tanggal ? \Carbon\Carbon::parse($sesiUjian->tanggal)->format('d M Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Kapasitas</td>
                            <td>{{ $ruangUjian->kapasitas ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Terisi</td>
                            <td>
                                <strong>{{ $pesertaList->count() }}</strong>
                                @if($ruangUjian->kapasitas)
                                    / {{ $ruangUjian->kapasitas }}
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Cari & Tambah Peserta --}}
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user-plus mr-1"></i>Tambah Peserta</h3>
                </div>
                <div class="card-body">
                    <div class="position-relative">
                        <div class="input-group">
                            <input type="text" class="form-control" id="searchPendaftar" 
                                   placeholder="Cari nama / NISN / No. Pendaftaran..." autocomplete="off">
                            <div class="input-group-append">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                        </div>
                        <div class="search-results-dropdown" id="searchResults"></div>
                    </div>
                    <small class="text-muted mt-1 d-block">Ketik minimal 2 karakter untuk mencari</small>

                    <form method="POST" 
                          action="{{ route('admin.sesi-ujian.tambah-peserta', [$sesiUjian->id, $ruangUjian->id]) }}" 
                          id="tambahForm" style="display:none;" class="mt-3">
                        @csrf
                        <input type="hidden" name="calon_siswa_id" id="selectedPesertaId">
                        <div class="alert alert-info py-2 mb-2" id="selectedInfo"></div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success btn-sm flex-grow-1">
                                <i class="fas fa-plus mr-1"></i>Tambahkan
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="cancelSelect()">Batal</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Daftar Ruangan Lain --}}
            <div class="card card-outline card-secondary">
                <div class="card-header py-2">
                    <h3 class="card-title" style="font-size:13px;">
                        <i class="fas fa-exchange-alt mr-1"></i>Ruangan Lain di Sesi Ini
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        @foreach($semuaRuangan as $r)
                        <tr class="{{ $r->id == $ruangUjian->id ? 'bg-light' : '' }}">
                            <td>
                                @if($r->id == $ruangUjian->id)
                                    <strong><i class="fas fa-arrow-right text-primary mr-1"></i>{{ $r->nama_ruang }}</strong>
                                @else
                                    <a href="{{ route('admin.sesi-ujian.peserta-ruang', [$sesiUjian->id, $r->id]) }}">
                                        {{ $r->nama_ruang }}
                                    </a>
                                @endif
                            </td>
                            <td class="text-right">
                                <span class="badge badge-{{ $r->id == $ruangUjian->id ? 'primary' : 'secondary' }} badge-room">
                                    {{ $r->jumlah_peserta ?? 0 }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>

        {{-- Daftar Peserta --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-users mr-1"></i>Daftar Peserta ({{ $pesertaList->count() }})
                    </h3>
                </div>
                <div class="card-body p-0">
                    @if($pesertaList->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-center" style="width:50px;">No</th>
                                    <th>Nama</th>
                                    <th>NISN</th>
                                    <th>Asal Sekolah</th>
                                    <th class="text-center" style="width:120px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pesertaList as $p)
                                <tr class="peserta-row">
                                    <td class="text-center"><strong>{{ $p->nomor_urut }}</strong></td>
                                    <td>
                                        <strong>{{ $p->calonSiswa->nama_lengkap ?? '-' }}</strong>
                                        <br><small class="text-muted">{{ $p->calonSiswa->no_pendaftaran ?? '' }}</small>
                                    </td>
                                    <td>{{ $p->calonSiswa->nisn ?? '-' }}</td>
                                    <td><small>{{ $p->calonSiswa->nama_sekolah_asal ?? '-' }}</small></td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            {{-- Pindah --}}
                                            <button type="button" class="btn btn-warning btn-xs" 
                                                    data-toggle="modal" 
                                                    data-target="#pindahModal{{ $p->id }}"
                                                    title="Pindah Ruangan">
                                                <i class="fas fa-exchange-alt"></i>
                                            </button>
                                            {{-- Input Nilai --}}
                                            <a href="{{ route('admin.sesi-ujian.admin-input-nilai', [$sesiUjian->id, $ruangUjian->id, $p->id]) }}" 
                                               class="btn btn-info btn-xs" title="Input Nilai">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            {{-- Hapus --}}
                                            <form method="POST" 
                                                  action="{{ route('admin.sesi-ujian.hapus-peserta', [$sesiUjian->id, $ruangUjian->id, $p->id]) }}"
                                                  style="display:inline;"
                                                  onsubmit="return confirm('Hapus peserta {{ $p->calonSiswa->nama_lengkap ?? '' }} dari ruangan?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-xs" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                {{-- Modal Pindah Ruangan --}}
                                <div class="modal fade" id="pindahModal{{ $p->id }}">
                                    <div class="modal-dialog modal-sm">
                                        <div class="modal-content">
                                            <div class="modal-header bg-warning py-2">
                                                <h5 class="modal-title" style="font-size:14px;">
                                                    <i class="fas fa-exchange-alt mr-1"></i>Pindah Ruangan
                                                </h5>
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            </div>
                                            <form method="POST" 
                                                  action="{{ route('admin.sesi-ujian.pindah-peserta', [$sesiUjian->id, $ruangUjian->id, $p->id]) }}">
                                                @csrf
                                                <div class="modal-body">
                                                    <p class="mb-2"><strong>{{ $p->calonSiswa->nama_lengkap ?? '-' }}</strong></p>
                                                    <div class="form-group mb-0">
                                                        <label>Pindah ke:</label>
                                                        <select name="ruangan_tujuan_id" class="form-control form-control-sm" required>
                                                            <option value="">-- Pilih Ruangan --</option>
                                                            @foreach($semuaRuangan as $r)
                                                                @if($r->id != $ruangUjian->id)
                                                                    <option value="{{ $r->id }}">
                                                                        {{ $r->nama_ruang }} ({{ $r->jumlah_peserta ?? 0 }} peserta)
                                                                    </option>
                                                                @endif
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer py-2">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-warning btn-sm">
                                                        <i class="fas fa-exchange-alt mr-1"></i>Pindahkan
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-2"></i>
                            <p class="text-muted">Belum ada peserta di ruangan ini</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    var searchTimeout;
    
    $('#searchPendaftar').on('input', function() {
        var q = $(this).val().trim();
        clearTimeout(searchTimeout);
        
        if (q.length < 2) {
            $('#searchResults').hide().empty();
            return;
        }

        searchTimeout = setTimeout(function() {
            $.get('{{ route("admin.sesi-ujian.cari-pendaftar", $sesiUjian->id) }}', { q: q })
                .done(function(data) {
                    var html = '';
                    if (data.length === 0) {
                        html = '<div class="search-result-item text-muted text-center"><i class="fas fa-search mr-1"></i>Tidak ditemukan</div>';
                    } else {
                        data.forEach(function(item) {
                            html += '<div class="search-result-item" onclick="selectPeserta(\'' + item.id + '\', \'' + escapeHtml(item.nama_lengkap) + '\', \'' + (item.nisn || '-') + '\', \'' + (item.no_pendaftaran || '-') + '\')">';
                            html += '<strong>' + escapeHtml(item.nama_lengkap) + '</strong>';
                            html += '<br><small class="text-muted">';
                            html += 'NISN: ' + (item.nisn || '-') + ' &middot; No: ' + (item.no_pendaftaran || '-');
                            if (item.nama_sekolah_asal) html += ' &middot; ' + escapeHtml(item.nama_sekolah_asal);
                            html += '</small></div>';
                        });
                    }
                    $('#searchResults').html(html).show();
                });
        }, 300);
    });

    // Close dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#searchPendaftar, #searchResults').length) {
            $('#searchResults').hide();
        }
    });
});

function selectPeserta(id, nama, nisn, noPendaftaran) {
    $('#selectedPesertaId').val(id);
    $('#selectedInfo').html('<strong>' + nama + '</strong><br><small>NISN: ' + nisn + ' | No: ' + noPendaftaran + '</small>');
    $('#tambahForm').show();
    $('#searchResults').hide();
    $('#searchPendaftar').val('');
}

function cancelSelect() {
    $('#tambahForm').hide();
    $('#selectedPesertaId').val('');
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>
@stop
