@extends('adminlte::page')

@section('title', 'Rekap Nilai Seleksi')

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap4.min.css">
<style>
    .nilai-cell {
        font-weight: bold;
    }
    .rank-badge {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
    .rank-1 { background: gold; color: #000; }
    .rank-2 { background: silver; color: #000; }
    .rank-3 { background: #cd7f32; color: #fff; }
</style>
@stop

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">
            <i class="fas fa-trophy mr-2"></i>Rekap Nilai Seleksi
        </h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.nilai-seleksi.index') }}">Nilai Seleksi</a></li>
            <li class="breadcrumb-item active">Rekap</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">

    <!-- Summary -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $rekapData->count() }}</h3>
                    <p>Total Peserta</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $rekapData->count() > 0 ? number_format($rekapData->avg('nilai_akhir') ?? 0, 2) : '0.00' }}</h3>
                    <p>Rata-rata Nilai Akhir</p>
                </div>
                <div class="icon"><i class="fas fa-chart-line"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $rekapData->count() > 0 ? number_format($rekapData->max('nilai_akhir') ?? 0, 2) : '0.00' }}</h3>
                    <p>Nilai Akhir Tertinggi</p>
                </div>
                <div class="icon"><i class="fas fa-arrow-up"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $rekapData->count() > 0 ? number_format($rekapData->min('nilai_akhir') ?? 0, 2) : '0.00' }}</h3>
                    <p>Nilai Akhir Terendah</p>
                </div>
                <div class="icon"><i class="fas fa-arrow-down"></i></div>
            </div>
        </div>
    </div>

    <!-- Detail Pendaftar -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-chart-pie mr-2"></i>Detail Pendaftar</h3>
        </div>
        <div class="card-body">
            {{-- Jalur Pendaftaran --}}
            <h6 class="text-muted text-uppercase mb-3"><i class="fas fa-road mr-1"></i> Jalur Pendaftaran</h6>
            <div class="row mb-3">
                @foreach($detailStats['jalur'] as $namaJalur => $stat)
                    <div class="col-md-4 col-6 mb-3">
                        <div class="info-box mb-0">
                            <span class="info-box-icon {{ str_contains(strtolower($namaJalur), 'asrama') ? 'bg-success' : 'bg-primary' }}">
                                <i class="fas {{ str_contains(strtolower($namaJalur), 'asrama') ? 'fa-home' : 'fa-school' }}"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ $namaJalur }}</span>
                                <span class="info-box-number">{{ $stat['total'] }} pendaftar</span>
                                <small class="text-muted">
                                    <i class="fas fa-mars text-primary"></i> {{ $stat['laki_laki'] }} L
                                    &nbsp;|&nbsp;
                                    <i class="fas fa-venus text-danger"></i> {{ $stat['perempuan'] }} P
                                </small>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Minat Program --}}
            <h6 class="text-muted text-uppercase mb-3"><i class="fas fa-heart mr-1"></i> Minat Program (Pilihan Pendaftar)</h6>
            <div class="row mb-3">
                @foreach($detailStats['minat'] as $namaMinat => $stat)
                    <div class="col-md-4 col-6 mb-3">
                        <div class="info-box mb-0">
                            @php
                                $isAsrama = str_contains(strtolower($namaMinat), 'asrama');
                                $isReguler = str_contains(strtolower($namaMinat), 'reguler');
                            @endphp
                            <span class="info-box-icon {{ $isAsrama ? 'bg-purple' : ($isReguler ? 'bg-teal' : 'bg-gray') }}">
                                <i class="fas {{ $isAsrama ? 'fa-home' : ($isReguler ? 'fa-school' : 'fa-question') }}"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Minat {{ $namaMinat }}</span>
                                <span class="info-box-number">{{ $stat['total'] }} pendaftar</span>
                                <small class="text-muted">
                                    <i class="fas fa-mars text-primary"></i> {{ $stat['laki_laki'] }} L
                                    &nbsp;|&nbsp;
                                    <i class="fas fa-venus text-danger"></i> {{ $stat['perempuan'] }} P
                                </small>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <hr>
            {{-- Total Gender --}}
            <div class="row">
                <div class="col-md-4 col-6">
                    <div class="info-box mb-0">
                        <span class="info-box-icon bg-secondary">
                            <i class="fas fa-venus-mars"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Keseluruhan</span>
                            <span class="info-box-number">{{ $detailStats['total'] }} pendaftar</span>
                            <small class="text-muted">
                                <i class="fas fa-mars text-primary"></i> {{ $detailStats['laki_laki'] }} Laki-laki
                                &nbsp;|&nbsp;
                                <i class="fas fa-venus text-danger"></i> {{ $detailStats['perempuan'] }} Perempuan
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Keterangan Bobot -->
    <div class="callout callout-info">
        <h5><i class="fas fa-info-circle mr-1"></i> Formula Nilai Akhir</h5>
        <p class="mb-0">
            <strong>Nilai Akhir</strong> = (CBT × <strong>50%</strong>) + (Rapor × <strong>10%</strong>) + (Seleksi × <strong>40%</strong>)
            &nbsp;|&nbsp; <em>Minat</em> sebagai tiebreaker &nbsp;|&nbsp; <em>Sertifikat</em> sebagai referensi tambahan
        </p>
    </div>

    <!-- Data Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-table mr-2"></i>Data Rekap Nilai</h3>
        </div>
        <div class="card-body">
            {{-- Filter inline --}}
            <form method="GET" action="{{ route('admin.nilai-seleksi.rekap') }}" class="mb-3">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label class="mb-1 small">Tahun Pelajaran</label>
                        <select name="tahun_pelajaran_id" class="form-control form-control-sm select2">
                            <option value="">-- Semua --</option>
                            @foreach($tahunPelajarans as $tp)
                                <option value="{{ $tp->id }}" {{ request('tahun_pelajaran_id') == $tp->id ? 'selected' : '' }}>
                                    {{ $tp->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="mb-1 small">Jalur</label>
                        <select name="jalur_id" class="form-control form-control-sm select2">
                            <option value="">-- Semua --</option>
                            @foreach($jalurs as $jalur)
                                <option value="{{ $jalur->id }}" {{ request('jalur_id') == $jalur->id ? 'selected' : '' }}>
                                    {{ $jalur->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="mb-1 small">Status</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="">-- Semua --</option>
                            <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                            <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="mb-1 small">Jenis Tes</label>
                        <select name="jenis_tes" class="form-control form-control-sm">
                            <option value="">-- Semua --</option>
                            <option value="wawancara" {{ request('jenis_tes') == 'wawancara' ? 'selected' : '' }}>Wawancara</option>
                            <option value="cbt" {{ request('jenis_tes') == 'cbt' ? 'selected' : '' }}>CBT</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-search mr-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.nilai-seleksi.rekap') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-sync mr-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
            <div style="overflow-x: auto;">
            <table id="rekapTable" class="table table-bordered table-striped" style="font-size: 0.8rem;">
                <thead>
                    <tr>
                        <th class="text-center" width="40" rowspan="2">Rank</th>
                        <th rowspan="2">No. Tes</th>
                        <th rowspan="2">NISN</th>
                        <th rowspan="2">Nama Peserta</th>
                        <th rowspan="2">JK</th>
                        <th rowspan="2">Jalur</th>
                        <th rowspan="2" title="Minat/Pilihan Program">Pilihan</th>
                        <th class="text-center" colspan="4" style="background: #e8f5e9;">Seleksi (40%)</th>
                        <th class="text-center" rowspan="2" style="background: #e8f5e9;">T. Seleksi</th>
                        <th class="text-center" colspan="4" style="background: #e3f2fd;">CBT (50%)</th>
                        <th class="text-center" rowspan="2" style="background: #e3f2fd;">Rata CBT</th>
                        <th class="text-center" rowspan="2" style="background: #fff3e0;">Rapor (10%)</th>
                        <th class="text-center" rowspan="2" style="background: #fce4ec;" title="Nilai Akhir = CBT×50% + Rapor×10% + Seleksi×40%">Nilai Akhir</th>
                        <th class="text-center" rowspan="2" title="Minat terhadap pilihan program (tiebreaker)">Minat</th>
                        <th class="text-center" rowspan="2" title="Hafalan Juz (rekomendasi)">Rekomendasi</th>
                        <th class="text-center" rowspan="2" title="Sertifikat/Piagam prestasi (referensi)">Sertifikat</th>
                        <th class="text-center" rowspan="2">Status</th>
                    </tr>
                    <tr>
                        <th class="text-center" style="background: #e8f5e9;">Baca</th>
                        <th class="text-center" style="background: #e8f5e9;">Tulis</th>
                        <th class="text-center" style="background: #e8f5e9;">Hafalan</th>
                        <th class="text-center" style="background: #e8f5e9;">Juz</th>
                        <th class="text-center" style="background: #e3f2fd;">MTK</th>
                        <th class="text-center" style="background: #e3f2fd;">IPA</th>
                        <th class="text-center" style="background: #e3f2fd;">IPS</th>
                        <th class="text-center" style="background: #e3f2fd;">B.Ing</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rekapData as $index => $nilai)
                        @php
                            $cbt = $cbtData[$nilai->calon_siswa_id] ?? null;
                            $sertifikats = $sertifikatData[$nilai->calon_siswa_id] ?? collect();
                        @endphp
                        <tr>
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
                                    <span class="text-primary"><i class="fas fa-mars"></i> L</span>
                                @else
                                    <span class="text-danger"><i class="fas fa-venus"></i> P</span>
                                @endif
                            </td>
                            <td>{{ $nilai->sesiUjian->jalur->nama ?? '-' }}</td>
                            {{-- Pilihan Program --}}
                            <td class="text-center">
                                @if($nilai->calonSiswa?->pilihan_program === 'Asrama')
                                    <span class="badge badge-purple" style="background:#6f42c1;color:#fff;">Asrama</span>
                                @elseif($nilai->calonSiswa?->pilihan_program === 'Reguler')
                                    <span class="badge badge-teal" style="background:#20c997;color:#fff;">Reguler</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            {{-- Seleksi --}}
                            <td class="text-center nilai-cell">{{ $nilai->nilai_baca_quran ?? '-' }}</td>
                            <td class="text-center nilai-cell">{{ $nilai->nilai_tulis_quran ?? '-' }}</td>
                            <td class="text-center nilai-cell">{{ $nilai->nilai_hafalan ?? '-' }}</td>
                            <td class="text-center">{{ $nilai->jumlah_juz_hafalan ?? '-' }}</td>
                            <td class="text-center">
                                <span class="badge badge-success" style="font-size: 0.85rem;">
                                    {{ number_format($nilai->total_nilai, 2) }}
                                </span>
                            </td>
                            {{-- CBT --}}
                            <td class="text-center">{{ $cbt ? $cbt->nilai_mtk : '-' }}</td>
                            <td class="text-center">{{ $cbt ? $cbt->nilai_ipa : '-' }}</td>
                            <td class="text-center">{{ $cbt ? $cbt->nilai_ips : '-' }}</td>
                            <td class="text-center">{{ $cbt ? $cbt->nilai_bahasa_inggris : '-' }}</td>
                            <td class="text-center">
                                @if($cbt)
                                    <span class="badge badge-info" style="font-size: 0.85rem;">
                                        {{ number_format($cbt->rata_rata, 2) }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            {{-- Rapor --}}
                            <td class="text-center">
                                @if($nilai->nilai_rapor_rata !== null)
                                    <span class="badge badge-warning" style="font-size: 0.85rem;">
                                        {{ number_format($nilai->nilai_rapor_rata, 2) }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            {{-- Nilai Akhir --}}
                            <td class="text-center">
                                <span class="badge badge-danger" style="font-size: 1rem; font-weight: bold;">
                                    {{ number_format($nilai->nilai_akhir, 2) }}
                                </span>
                            </td>
                            {{-- Minat --}}
                            <td class="text-center">
                                <span class="badge badge-info">{{ $nilai->nilai_wawancara ?? '-' }}</span>
                            </td>
                            {{-- Rekomendasi (Hafalan Juz) --}}
                            <td class="text-center">
                                @if($nilai->jumlah_juz_hafalan && $nilai->jumlah_juz_hafalan >= 10)
                                    <span class="badge badge-success" title="Hafalan {{ $nilai->jumlah_juz_hafalan }} Juz">{{ $nilai->jumlah_juz_hafalan }} Juz <i class="fas fa-star"></i></span>
                                @elseif($nilai->jumlah_juz_hafalan && $nilai->jumlah_juz_hafalan >= 5)
                                    <span class="badge badge-primary" title="Hafalan {{ $nilai->jumlah_juz_hafalan }} Juz">{{ $nilai->jumlah_juz_hafalan }} Juz</span>
                                @elseif($nilai->jumlah_juz_hafalan)
                                    <span class="badge badge-secondary" title="Hafalan {{ $nilai->jumlah_juz_hafalan }} Juz">{{ $nilai->jumlah_juz_hafalan }} Juz</span>
                                @else
                                    -
                                @endif
                            </td>
                            {{-- Sertifikat --}}
                            <td class="text-center">
                                @if($sertifikats->count() > 0)
                                    <span class="badge badge-secondary" title="{{ $sertifikats->pluck('nama_dokumen')->join(', ') }}" style="cursor: help;">
                                        {{ $sertifikats->count() }} <i class="fas fa-certificate"></i>
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            {{-- Status --}}
                            <td class="text-center">
                                @if($nilai->status == 'verified')
                                    <span class="badge badge-success">Verified</span>
                                @else
                                    <span class="badge badge-warning">{{ ucfirst($nilai->status) }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>{{-- overflow-x --}}
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4'
    });

    // Initialize DataTable
    $('#rekapTable').DataTable({
        dom: 'Bfrtip',
        orderCellsTop: true,
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel mr-1"></i> Export Excel',
                className: 'btn btn-success btn-sm',
                title: 'Rekap Nilai Seleksi PPDB',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print mr-1"></i> Print',
                className: 'btn btn-info btn-sm',
                title: 'Rekap Nilai Seleksi PPDB'
            }
        ],
        order: [[18, 'desc'], [19, 'desc']], // Sort by nilai akhir desc, then minat desc
        pageLength: 25,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json'
        }
    });
});
</script>
@stop
