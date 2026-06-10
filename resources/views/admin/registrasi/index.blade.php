@extends('adminlte::page')

@section('title', 'Data Registrasi')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-clipboard-check mr-2"></i>Data Registrasi</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Registrasi</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Statistik --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner"><h3>{{ $totalLulus }}</h3><p>Total Lulus</p></div>
                <div class="icon"><i class="fas fa-graduation-cap"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner"><h3>{{ $totalRegistrasi }}</h3><p>Sudah Registrasi</p></div>
                <div class="icon"><i class="fas fa-user-check"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner"><h3>{{ $belumRegistrasi }}</h3><p>Belum Registrasi</p></div>
                <div class="icon"><i class="fas fa-user-clock"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner"><h3>{{ $totalKonflik }}</h3><p>Konflik Jurusan</p></div>
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
        </div>
    </div>

    {{-- Filter & Aksi --}}
    <div class="card card-outline card-info">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.registrasi.index') }}" class="row">
                <div class="col-md-3">
                    <label class="mb-1">Tahun Pelajaran</label>
                    <select name="tahun_pelajaran_id" class="form-control form-control-sm">
                        @foreach($tahunPelajarans as $tahun)
                            <option value="{{ $tahun->id }}" {{ $selectedTahunIdInput == $tahun->id ? 'selected' : '' }}>
                                {{ $tahun->nama }}{{ $tahun->is_active ? ' (Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="mb-1">Status Match</label>
                    <select name="match_status" class="form-control form-control-sm">
                        <option value="">Semua</option>
                        <option value="matched_exact" {{ $filterStatus === 'matched_exact' ? 'selected' : '' }}>Cocok Persis</option>
                        <option value="matched_fuzzy" {{ $filterStatus === 'matched_fuzzy' ? 'selected' : '' }}>Mirip</option>
                        <option value="conflict_jurusan" {{ $filterStatus === 'conflict_jurusan' ? 'selected' : '' }}>Konflik Jurusan</option>
                        <option value="manual" {{ $filterStatus === 'manual' ? 'selected' : '' }}>Manual</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="mb-1">Cari (nama / no. tes / notes)</label>
                    <input type="text" name="q" value="{{ $searchQ }}" class="form-control form-control-sm" placeholder="Ketik kata kunci...">
                </div>
                <div class="col-md-3 d-flex align-items-end justify-content-between">
                    <div>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter mr-1"></i>Terapkan</button>
                        <a href="{{ route('admin.registrasi.index') }}" class="btn btn-default btn-sm"><i class="fas fa-undo"></i></a>
                    </div>
                    <a href="{{ route('admin.registrasi.upload', ['tahun_pelajaran_id' => $selectedTahunIdInput]) }}" class="btn btn-success btn-sm">
                        <i class="fas fa-file-import mr-1"></i>Import Excel
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel data --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-table mr-2"></i>Daftar Pendaftar Teregistrasi ({{ $registrasis->total() }})</h3>
        </div>
        <div class="card-body p-0" style="overflow-x:auto;">
            <table class="table table-bordered table-hover table-sm mb-0">
                <thead class="bg-dark text-white">
                    <tr>
                        <th width="50" class="text-center">No</th>
                        <th>Notes</th>
                        <th>Nama Pendaftar</th>
                        <th>No. Tes</th>
                        <th>Gelombang</th>
                        <th>Jurusan</th>
                        <th class="text-center">Status</th>
                        <th>Tgl. Registrasi</th>
                        <th width="70" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrasis as $i => $reg)
                        @php
                            $badge = match($reg->match_status) {
                                'matched_exact' => ['success', 'Cocok'],
                                'matched_fuzzy' => ['info', 'Mirip'],
                                'conflict_jurusan' => ['warning', 'Konflik Jurusan'],
                                default => ['secondary', 'Manual'],
                            };
                        @endphp
                        <tr>
                            <td class="text-center">{{ $registrasis->firstItem() + $i }}</td>
                            <td><code>{{ $reg->notes ?: '-' }}</code></td>
                            <td>
                                {{ $reg->calonSiswa?->nama_lengkap ?? $reg->nama_excel ?? '-' }}
                                @if($reg->nama_excel && $reg->calonSiswa && strcasecmp($reg->nama_excel, $reg->calonSiswa->nama_lengkap) !== 0)
                                    <br><small class="text-muted">Excel: {{ $reg->nama_excel }}</small>
                                @endif
                            </td>
                            <td>{{ $reg->calonSiswa?->nomor_tes ?? '-' }}</td>
                            <td>{{ $reg->calonSiswa?->gelombangPendaftaran?->nama ?? '-' }}</td>
                            <td>
                                {{ $reg->jurusan_final ?: '-' }}
                                @if($reg->pindah_jurusan)
                                    <br><small class="text-warning"><i class="fas fa-exchange-alt"></i> dari {{ $reg->jurusan_awal ?: '-' }}</small>
                                @elseif($reg->match_status === 'conflict_jurusan' && $reg->jurusan_excel)
                                    <i class="fas fa-info-circle text-warning" title="Pindah jurusan dari hasil seleksi"></i>
                                @endif
                            </td>
                            <td class="text-center"><span class="badge badge-{{ $badge[0] }}">{{ $badge[1] }}</span></td>
                            <td>{{ $reg->tanggal_registrasi?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td class="text-center">
                                <form action="{{ route('admin.registrasi.destroy', $reg->id) }}" method="POST" onsubmit="return confirm('Hapus data registrasi ini?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">Belum ada data registrasi. Silakan <a href="{{ route('admin.registrasi.upload') }}">import dari Excel</a>.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($registrasis->hasPages())
            <div class="card-footer">{{ $registrasis->links() }}</div>
        @endif
    </div>
</div>
@endsection
