@extends('adminlte::page')

@section('title', 'Statistik Ekonomi Keluarga')

@section('css')
<style>
    .chart-container { position: relative; height: 300px; }
    .table-stat th, .table-stat td { padding: 8px 12px; font-size: 13px; }
    .category-badge { font-size: 11px; padding: 3px 8px; border-radius: 3px; }
    .ekonomi-rendah { background: #fce4ec; color: #c62828; }
    .ekonomi-menengah-bawah { background: #fff3e0; color: #e65100; }
    .ekonomi-menengah { background: #fff8e1; color: #f9a825; }
    .ekonomi-menengah-atas { background: #e8f5e9; color: #2e7d32; }
    .ekonomi-atas { background: #e3f2fd; color: #1565c0; }
</style>
@stop

@section('content_header')
<div class="row align-items-center">
    <div class="col-sm-6">
        <h1><i class="fas fa-money-bill-wave"></i> Statistik Ekonomi Keluarga</h1>
    </div>
    <div class="col-sm-6">
        <div class="d-flex justify-content-sm-end align-items-center" style="gap: 10px;">
            <a href="{{ route('admin.statistik.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <form class="form-inline">
                <select name="tahun_pelajaran_id" class="form-control form-control-sm" onchange="this.form.submit()">
                    @foreach($tahunPelajaranList as $tp)
                    <option value="{{ $tp->id }}" {{ $tahunAktif && $tahunAktif->id == $tp->id ? 'selected' : '' }}>
                        {{ $tp->nama }}
                    </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>
</div>
@stop

@section('content')
{{-- Ringkasan Penghasilan --}}
<div class="row">
    <div class="col-12">
        <div class="callout callout-info">
            <h5><i class="fas fa-info-circle"></i> Kategori Penghasilan</h5>
            <p class="mb-0">
                <span class="category-badge ekonomi-rendah">&lt; 1 juta</span>
                <span class="category-badge ekonomi-menengah-bawah">1-3 juta</span>
                <span class="category-badge ekonomi-menengah">3-5 juta</span>
                <span class="category-badge ekonomi-menengah-atas">5-10 juta</span>
                <span class="category-badge ekonomi-atas">&gt; 10 juta</span>
            </p>
        </div>
    </div>
</div>

<div class="row">
    {{-- Penghasilan Ayah Chart --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-male"></i> Penghasilan Ayah</h3>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="penghasilanAyahChart"></canvas>
                </div>
            </div>
            <div class="card-footer p-0">
                <table class="table table-striped table-stat mb-0">
                    <tbody>
                        @php $totalAyah = array_sum($byPenghasilanAyah); @endphp
                        @foreach($byPenghasilanAyah as $kategori => $total)
                        <tr>
                            <td>{{ $kategori }}</td>
                            <td class="text-center"><strong>{{ $total }}</strong></td>
                            <td class="text-right">{{ $totalAyah > 0 ? round(($total / $totalAyah) * 100, 1) : 0 }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    {{-- Penghasilan Ibu Chart --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-female"></i> Penghasilan Ibu</h3>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="penghasilanIbuChart"></canvas>
                </div>
            </div>
            <div class="card-footer p-0">
                <table class="table table-striped table-stat mb-0">
                    <tbody>
                        @php $totalIbu = array_sum($byPenghasilanIbu); @endphp
                        @foreach($byPenghasilanIbu as $kategori => $total)
                        <tr>
                            <td>{{ $kategori }}</td>
                            <td class="text-center"><strong>{{ $total }}</strong></td>
                            <td class="text-right">{{ $totalIbu > 0 ? round(($total / $totalIbu) * 100, 1) : 0 }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Pekerjaan Ayah --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-briefcase"></i> Pekerjaan Ayah</h3>
            </div>
            <div class="card-body p-0" style="max-height: 350px; overflow-y: auto;">
                <table class="table table-striped table-stat mb-0">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>Pekerjaan</th>
                            <th class="text-center" width="80">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($byPekerjaanAyah as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $item->pekerjaan_ayah ?? 'Tidak Diketahui' }}</td>
                            <td class="text-center"><strong>{{ $item->total }}</strong></td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted">Tidak ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    {{-- Pekerjaan Ibu --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-briefcase"></i> Pekerjaan Ibu</h3>
            </div>
            <div class="card-body p-0" style="max-height: 350px; overflow-y: auto;">
                <table class="table table-striped table-stat mb-0">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>Pekerjaan</th>
                            <th class="text-center" width="80">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($byPekerjaanIbu as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $item->pekerjaan_ibu ?? 'Tidak Diketahui' }}</td>
                            <td class="text-center"><strong>{{ $item->total }}</strong></td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted">Tidak ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Pendidikan Ayah --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-graduation-cap"></i> Pendidikan Ayah</h3>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="pendidikanAyahChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Pendidikan Ibu --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-graduation-cap"></i> Pendidikan Ibu</h3>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="pendidikanIbuChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    var colors = ['#dc3545', '#fd7e14', '#ffc107', '#28a745', '#007bff', '#6c757d'];
    
    // Penghasilan Ayah
    var penghasilanAyahData = @json($byPenghasilanAyah);
    var penghasilanAyahLabels = Object.keys(penghasilanAyahData);
    var penghasilanAyahValues = Object.values(penghasilanAyahData);
    new Chart(document.getElementById('penghasilanAyahChart'), {
        type: 'doughnut',
        data: {
            labels: penghasilanAyahLabels,
            datasets: [{
                data: penghasilanAyahValues,
                backgroundColor: colors,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right' }
            }
        }
    });
    
    // Penghasilan Ibu
    var penghasilanIbuData = @json($byPenghasilanIbu);
    var penghasilanIbuLabels = Object.keys(penghasilanIbuData);
    var penghasilanIbuValues = Object.values(penghasilanIbuData);
    new Chart(document.getElementById('penghasilanIbuChart'), {
        type: 'doughnut',
        data: {
            labels: penghasilanIbuLabels,
            datasets: [{
                data: penghasilanIbuValues,
                backgroundColor: colors,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right' }
            }
        }
    });
    
    // Pendidikan Ayah
    var pendidikanAyahData = @json($byPendidikanAyah);
    new Chart(document.getElementById('pendidikanAyahChart'), {
        type: 'bar',
        data: {
            labels: pendidikanAyahData.map(d => d.pendidikan_ayah || 'Tidak Diketahui'),
            datasets: [{
                label: 'Jumlah',
                data: pendidikanAyahData.map(d => d.total),
                backgroundColor: '#007bff',
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
    
    // Pendidikan Ibu
    var pendidikanIbuData = @json($byPendidikanIbu);
    new Chart(document.getElementById('pendidikanIbuChart'), {
        type: 'bar',
        data: {
            labels: pendidikanIbuData.map(d => d.pendidikan_ibu || 'Tidak Diketahui'),
            datasets: [{
                label: 'Jumlah',
                data: pendidikanIbuData.map(d => d.total),
                backgroundColor: '#e83e8c',
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
</script>
@stop
