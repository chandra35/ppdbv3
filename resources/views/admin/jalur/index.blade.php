@extends('adminlte::page')

@section('title', 'Jalur Pendaftaran')

@section('css')
@include('admin.partials.action-buttons-style')
<style>
    .card-footer .action-btns-full .btn {
        font-size: 0.8rem;
        padding: 0.4rem 0.6rem;
    }
</style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-road mr-2"></i>Jalur Pendaftaran</h1>
        <a href="{{ route('admin.jalur.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Tambah Jalur
        </a>
    </div>
@stop

@section('content')
{{-- Alert Jalur Aktif --}}
@php
    $jalurAktif = $jalurList->firstWhere('status', 'open');
@endphp
@if($jalurAktif)
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="icon fas fa-check-circle"></i>
    <strong>Jalur Aktif:</strong> {{ $jalurAktif->nama }} 
    ({{ $jalurAktif->tanggal_buka?->format('d/m/Y') }} - {{ $jalurAktif->tanggal_tutup?->format('d/m/Y') }})
    - Pendaftaran sedang dibuka
</div>
@else
<div class="alert alert-warning alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="icon fas fa-exclamation-triangle"></i>
    <strong>Perhatian:</strong> Belum ada jalur pendaftaran yang aktif/dibuka untuk tahun pelajaran {{ $tahunPelajaranSelected?->nama ?? '-' }}. 
    Pendaftar tidak bisa mendaftar sampai ada jalur yang diaktifkan.
</div>
@endif

<div class="row">
    {{-- Filter Tahun Pelajaran --}}
    <div class="col-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Daftar Jalur Pendaftaran</h3>
                    <form action="" method="GET" class="form-inline">
                        <label class="mr-2">Tahun Pelajaran:</label>
                        <select name="tahun_pelajaran_id" class="form-control form-control-sm" onchange="this.form.submit()">
                            @foreach($tahunPelajaranList as $tp)
                            <option value="{{ $tp->id }}" {{ $tahunPelajaranAktif == $tp->id ? 'selected' : '' }}>
                                {{ $tp->nama }}
                                @if($tp->is_active) (Aktif) @endif
                            </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
            <div class="card-body">
                @if($jalurList->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-road fa-4x text-muted mb-3"></i>
                    <p class="text-muted">Belum ada jalur pendaftaran untuk tahun pelajaran {{ $tahunPelajaranSelected?->nama ?? '-' }}</p>
                    <a href="{{ route('admin.jalur.create', ['tahun_pelajaran_id' => $tahunPelajaranAktif]) }}" class="btn btn-primary">
                        <i class="fas fa-plus mr-1"></i> Buat Jalur Pertama
                    </a>
                </div>
                @else
                <div class="row">
                    @foreach($jalurList as $jalur)
                    <div class="col-md-6 col-lg-4">
                        <div class="card card-outline card-{{ $jalur->status == 'open' ? 'success' : ($jalur->status == 'finished' ? 'secondary' : $jalur->warna) }} {{ $jalur->status == 'finished' ? 'bg-light' : '' }}">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="{{ $jalur->icon }} mr-1"></i>
                                    {{ $jalur->nama }}
                                </h3>
                                <div class="card-tools">
                                    @switch($jalur->status)
                                        @case('open')
                                            <span class="badge badge-success"><i class="fas fa-door-open mr-1"></i>Dibuka</span>
                                            @break
                                        @case('closed')
                                            <span class="badge badge-warning"><i class="fas fa-pause mr-1"></i>Ditutup Sementara</span>
                                            @break
                                        @case('finished')
                                            <span class="badge badge-secondary"><i class="fas fa-check mr-1"></i>Selesai</span>
                                            @break
                                        @default
                                            <span class="badge badge-light"><i class="fas fa-file mr-1"></i>Draft</span>
                                    @endswitch
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-2">{{ Str::limit($jalur->deskripsi, 80) ?: 'Tidak ada deskripsi' }}</p>
                                
                                {{-- Periode Pendaftaran --}}
                                @if($jalur->tanggal_buka || $jalur->tanggal_tutup)
                                <div class="mb-3">
                                    <small class="text-muted d-block">
                                        <i class="fas fa-calendar mr-1"></i> Periode:
                                    </small>
                                    <strong>
                                        {{ $jalur->tanggal_buka?->format('d M Y') ?? '-' }} 
                                        s/d 
                                        {{ $jalur->tanggal_tutup?->format('d M Y') ?? '-' }}
                                    </strong>
                                </div>
                                @endif
                                
                                {{-- Statistik --}}
                                <div class="row text-center mb-3">
                                    <div class="col-6">
                                        <span class="text-muted">Kuota</span>
                                        <h4 class="mb-0">{{ $jalur->kuota }}</h4>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-muted">Pendaftar</span>
                                        <h4 class="mb-0">{{ $jalur->pendaftar_count }}</h4>
                                    </div>
                                </div>
                                
                                {{-- Progress Bar --}}
                                <div class="progress mb-2" style="height: 8px;">
                                    <div class="progress-bar bg-{{ $jalur->persentaseKuota() >= 90 ? 'danger' : ($jalur->persentaseKuota() >= 70 ? 'warning' : 'success') }}" 
                                         style="width: {{ $jalur->persentaseKuota() }}%"></div>
                                </div>
                                <small class="text-muted">{{ $jalur->persentaseKuota() }}% terisi | Sisa: {{ $jalur->sisaKuota() }}</small>
                                
                                {{-- Gelombang --}}
                                <hr>
                                <h6><i class="fas fa-layer-group mr-1"></i> Gelombang ({{ $jalur->gelombang->count() }})</h6>
                                @if($jalur->gelombang->isEmpty())
                                    <p class="text-muted small">Belum ada gelombang</p>
                                @else
                                    @foreach($jalur->gelombang->take(3) as $gelombang)
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small>
                                            {{ $gelombang->nama }}
                                            @if(!$gelombang->tampil_nama_gelombang)
                                                <i class="fas fa-eye-slash text-muted" title="Tidak tampil di publik"></i>
                                            @endif
                                        </small>
                                        <span class="badge badge-{{ $gelombang->status_color }}">{{ $gelombang->status_label }}</span>
                                    </div>
                                    @endforeach
                                    @if($jalur->gelombang->count() > 3)
                                    <small class="text-muted">+{{ $jalur->gelombang->count() - 3 }} gelombang lainnya</small>
                                    @endif
                                @endif
                            </div>
                            <div class="card-footer">
                                {{-- Action Buttons berdasarkan Status --}}
                                <div class="action-btns-full mb-2">
                                    @switch($jalur->status)
                                        @case('draft')
                                            <form action="{{ route('admin.jalur.aktifkan', $jalur) }}" method="POST" class="d-inline flex-fill">
                                                @csrf
                                                <button type="submit" class="btn btn-action-success btn-block" onclick="return confirm('Buka pendaftaran untuk jalur ini?')">
                                                    <i class="fas fa-play mr-1"></i> Buka Pendaftaran
                                                </button>
                                            </form>
                                            @break
                                        @case('open')
                                            <form action="{{ route('admin.jalur.tutup', $jalur) }}" method="POST" class="d-inline flex-fill">
                                                @csrf
                                                <button type="submit" class="btn btn-action-warning btn-block" onclick="return confirm('Tutup sementara pendaftaran?')">
                                                    <i class="fas fa-pause mr-1"></i> Tutup
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.jalur.selesaikan', $jalur) }}" method="POST" class="d-inline flex-fill">
                                                @csrf
                                                <button type="submit" class="btn btn-action-secondary btn-block" onclick="return confirm('Selesaikan pendaftaran? Status akan menjadi Selesai dan tidak bisa dibuka lagi.')">
                                                    <i class="fas fa-check mr-1"></i> Selesai
                                                </button>
                                            </form>
                                            @break
                                        @case('closed')
                                            <form action="{{ route('admin.jalur.aktifkan', $jalur) }}" method="POST" class="d-inline flex-fill">
                                                @csrf
                                                <button type="submit" class="btn btn-action-success btn-block" onclick="return confirm('Buka kembali pendaftaran?')">
                                                    <i class="fas fa-play mr-1"></i> Buka
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.jalur.selesaikan', $jalur) }}" method="POST" class="d-inline flex-fill">
                                                @csrf
                                                <button type="submit" class="btn btn-action-secondary btn-block" onclick="return confirm('Selesaikan pendaftaran?')">
                                                    <i class="fas fa-check mr-1"></i> Selesai
                                                </button>
                                            </form>
                                            @break
                                        @case('finished')
                                            <button type="button" class="btn btn-light btn-block" disabled>
                                                <i class="fas fa-check-double mr-1"></i> Selesai
                                            </button>
                                            @break
                                    @endswitch
                                </div>
                                
                                {{-- Detail & Edit --}}
                                <div class="action-btns justify-content-center">
                                    <a href="{{ route('admin.jalur.show', $jalur) }}" class="btn btn-action-view" data-toggle="tooltip" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.jalur.edit', $jalur) }}" class="btn btn-action-edit" data-toggle="tooltip" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.jalur.duplicate', $jalur) }}" method="POST" class="d-inline action-form">
                                        @csrf
                                        <button type="submit" class="btn btn-action-primary" data-toggle="tooltip" title="Duplikasi" onclick="return confirm('Duplikasi jalur ini?')">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Info Card --}}
<div class="row">
    <div class="col-md-6">
        <div class="card card-outline card-info collapsed-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Tentang Jalur Pendaftaran</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <p><strong>Jalur Pendaftaran</strong> adalah periode pendaftaran yang dikelola admin:</p>
                <ul class="mb-0">
                    <li><strong>Jalur Prestasi</strong> - Pendaftaran untuk siswa berprestasi</li>
                    <li><strong>Jalur Reguler</strong> - Pendaftaran umum</li>
                    <li><strong>Jalur Afirmasi</strong> - Pendaftaran untuk siswa kurang mampu</li>
                    <li><strong>Jalur Zonasi</strong> - Berdasarkan zona tempat tinggal</li>
                </ul>
                <hr>
                <p class="mb-0"><strong>Catatan:</strong> Hanya <span class="badge badge-success">1 jalur</span> yang bisa aktif dalam satu waktu. Pendaftar akan otomatis masuk ke jalur yang sedang aktif.</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-outline card-warning collapsed-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-lightbulb mr-2"></i>Alur Penggunaan</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <ol class="mb-0">
                    <li><strong>Buat Jalur</strong> - Tambah jalur pendaftaran baru</li>
                    <li><strong>Atur Periode</strong> - Set tanggal buka dan tutup</li>
                    <li><strong>Buka Pendaftaran</strong> - Aktifkan jalur untuk menerima pendaftar</li>
                    <li><strong>Tutup Sementara</strong> - Pause pendaftaran jika diperlukan</li>
                    <li><strong>Selesaikan</strong> - Tandai jalur sudah selesai</li>
                    <li><strong>Buka Jalur Baru</strong> - Aktifkan jalur berikutnya (misal: Gelombang 2)</li>
                </ol>
                <hr>
                <p class="mb-0 text-muted"><small><i class="fas fa-info-circle mr-1"></i>Anda bisa membuat jalur dengan nama "Reguler Gel. 1", "Reguler Gel. 2" untuk mengelola gelombang.</small></p>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@stop
