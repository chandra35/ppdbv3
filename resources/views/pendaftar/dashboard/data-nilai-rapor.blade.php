@extends('layouts.pendaftar')

@section('title', 'Nilai Rapor')
@section('page-title', 'Nilai Rapor')

@section('breadcrumb')
<li class="breadcrumb-item active">Nilai Rapor</li>
@endsection

@section('css')
<style>
    .nilai-table {
        width: 100%;
        margin-bottom: 1rem;
    }
    .nilai-table th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 12px 8px;
        text-align: center;
        font-weight: 600;
        font-size: 14px;
    }
    .nilai-table td {
        padding: 8px 4px;
        text-align: center;
        vertical-align: middle;
    }
    .nilai-table tbody tr:hover {
        background-color: #f8f9fa;
    }
    .nilai-input {
        width: 100%;
        max-width: 100px;
        padding: 8px 4px;
        border: 2px solid #e9ecef;
        border-radius: 6px;
        text-align: center;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .nilai-input:focus {
        border-color: #667eea;
        outline: none;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    .nilai-input.is-invalid {
        border-color: #dc3545;
    }
    .rata-rata-display {
        font-size: 16px;
        font-weight: bold;
        color: #667eea;
        padding: 8px;
        background: linear-gradient(135deg, #f0f4ff 0%, #e8eeff 100%);
        border-radius: 8px;
        text-align: center;
    }
    .info-box {
        background: #f8f9fa;
        border-left: 3px solid #6c757d;
        padding: 0.75rem 1rem;
        border-radius: 4px;
        margin-bottom: 1.5rem;
    }
    .info-box h5 {
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #495057;
    }
    .info-box ul {
        margin: 0;
        padding-left: 1.2rem;
    }
    .info-box li {
        margin-bottom: 0.25rem;
        font-size: 12px;
        color: #6c757d;
    }
    .info-box li:last-child {
        margin-bottom: 0;
    }
    
    /* Mobile Card Layout */
    .semester-card {
        display: none;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        background: white;
    }
    .semester-card h5 {
        color: #667eea;
        font-weight: bold;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e9ecef;
    }
    .semester-card .form-group {
        margin-bottom: 12px;
    }
    .semester-card label {
        font-weight: 600;
        color: #495057;
        font-size: 13px;
        margin-bottom: 5px;
    }
    .semester-card .nilai-input {
        max-width: 100%;
        font-size: 16px;
        padding: 10px;
    }
    .semester-card .rata-rata-display {
        font-size: 18px;
        padding: 12px;
        margin-top: 10px;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .nilai-table {
            display: none;
        }
        .semester-card {
            display: block;
        }
        .card-header h3 {
            font-size: 16px;
        }
        .info-box h5 {
            font-size: 14px;
        }
        .info-box ul {
            font-size: 12px;
            padding-left: 1.2rem;
        }
        .info-box li {
            margin-bottom: 0.3rem;
        }
        .btn-lg {
            font-size: 14px;
            padding: 10px 16px;
        }
        .small-box .inner h3 {
            font-size: 28px;
        }
        .small-box .inner p {
            font-size: 13px;
        }
    }
    
    @media (max-width: 576px) {
        .card-body {
            padding: 0.75rem;
        }
        .info-box {
            padding: 0.75rem;
            font-size: 12px;
        }
        .semester-card {
            padding: 12px;
        }
        .semester-card h5 {
            font-size: 15px;
            margin-bottom: 12px;
        }
        .semester-card label {
            font-size: 12px;
        }
        .semester-card .nilai-input {
            font-size: 14px;
            padding: 8px;
        }
        .rata-rata-display {
            font-size: 14px;
        }
        .btn-lg {
            font-size: 13px;
            padding: 8px 12px;
        }
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <form action="{{ route('pendaftar.nilai-rapor.update') }}" method="POST" id="formNilaiRapor">
            @csrf
            @method('PUT')

            <div class="card">
                <div class="card-header bg-gradient-primary">
                    <h3 class="card-title text-white">
                        <i class="fas fa-graduation-cap mr-2"></i>
                        Input Nilai Rapor Semester 1-5
                    </h3>
                </div>
                <div class="card-body">
                    <div class="info-box">
                        <div style="font-size: 12px; font-weight: 600; margin-bottom: 0.5rem; color: #495057;">
                            <i class="fas fa-info-circle mr-1"></i>Petunjuk Pengisian<br>
                        </div>
                        <ul class="mb-0" style="line-height: 1.8;">
                            <li>Isikan nilai rapor <strong>dari Semester 1 hingga Semester 5</strong> (5 semester SMP).</li>
                            <li>Mata pelajaran yang dinilai: <strong>Matematika, Ilmu Pengetahuan Alam (IPA), dan Ilmu Pengetahuan Sosial (IPS)</strong>.</li>
                            <li>Nilai yang diinput adalah <strong>nilai akhir semester</strong> yang tertera pada raport.</li>
                            <li>Rentang nilai: <strong>1 sampai 100</strong> (angka bulat, tanpa desimal/koma).</li>
                            <li>Rata-rata per semester akan <strong>dihitung otomatis</strong> dari 3 mata pelajaran.</li>
                            <li>Pastikan semua nilai sudah diisi dengan benar sebelum menyimpan.</li>
                            <li><strong>Nilai rapor berkontribusi 30%</strong> terhadap penilaian akhir PPDB.</li>
                        </ul>
                    </div>

                    <div class="table-responsive">
                        <table class="nilai-table table table-bordered">
                            <thead>
                                <tr>
                                    <th width="15%">Semester</th>
                                    <th width="20%">Matematika</th>
                                    <th width="20%">IPA</th>
                                    <th width="20%">IPS</th>
                                    <th width="25%">Rata-Rata</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($nilaiRapor as $semester => $nilai)
                                <tr>
                                    <td>
                                        <strong>Semester {{ $semester }}</strong>
                                    </td>
                                    <td>
                                        <input type="number" 
                                               name="semester_{{ $semester }}_matematika" 
                                               class="nilai-input form-control @error("semester_{$semester}_matematika") is-invalid @enderror" 
                                               value="{{ old("semester_{$semester}_matematika", $nilai['matematika']) }}"
                                               min="1" 
                                               max="100" 
                                               step="1"
                                               required
                                               data-semester="{{ $semester }}"
                                               data-mapel="matematika"
                                               oninput="calculateRataRata({{ $semester }})">
                                        @error("semester_{$semester}_matematika")
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td>
                                        <input type="number" 
                                               name="semester_{{ $semester }}_ipa" 
                                               class="nilai-input form-control @error("semester_{$semester}_ipa") is-invalid @enderror" 
                                               value="{{ old("semester_{$semester}_ipa", $nilai['ipa']) }}"
                                               min="1" 
                                               max="100" 
                                               step="1"
                                               required
                                               data-semester="{{ $semester }}"
                                               data-mapel="ipa"
                                               oninput="calculateRataRata({{ $semester }})">
                                        @error("semester_{$semester}_ipa")
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td>
                                        <input type="number" 
                                               name="semester_{{ $semester }}_ips" 
                                               class="nilai-input form-control @error("semester_{$semester}_ips") is-invalid @enderror" 
                                               value="{{ old("semester_{$semester}_ips", $nilai['ips']) }}"
                                               min="1" 
                                               max="100" 
                                               step="1"
                                               required
                                               data-semester="{{ $semester }}"
                                               data-mapel="ips"
                                               oninput="calculateRataRata({{ $semester }})">
                                        @error("semester_{$semester}_ips")
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td>
                                        <div class="rata-rata-display" id="rata_rata_{{ $semester }}">
                                            {{ $nilai['rata_rata'] ? number_format($nilai['rata_rata'], 2) : '-' }}
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr style="background: #f8f9fa;">
                                    <td colspan="4" class="text-right"><strong>Rata-Rata Keseluruhan:</strong></td>
                                    <td>
                                        <div class="rata-rata-display" id="rata_rata_keseluruhan" style="font-size: 20px; color: #28a745;">
                                            {{ $calonSiswa->rata_rata_rapor ? number_format($calonSiswa->rata_rata_rapor, 2) : '-' }}
                                        </div>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    @foreach($nilaiRapor as $semester => $nilai)
                    <div class="semester-card">
                        <h5><i class="fas fa-book mr-2"></i>Semester {{ $semester }}</h5>
                        
                        <div class="form-group">
                            <label for="mobile_semester_{{ $semester }}_matematika">
                                <i class="fas fa-calculator mr-1"></i> Matematika
                            </label>
                            <input type="number" 
                                   id="mobile_semester_{{ $semester }}_matematika"
                                   name="semester_{{ $semester }}_matematika" 
                                   class="nilai-input form-control @error("semester_{$semester}_matematika") is-invalid @enderror" 
                                   value="{{ old("semester_{$semester}_matematika", $nilai['matematika']) }}"
                                   min="1" 
                                   max="100" 
                                   step="1"
                                   required
                                   data-semester="{{ $semester }}"
                                   data-mapel="matematika"
                                   oninput="calculateRataRata({{ $semester }})">
                            @error("semester_{$semester}_matematika")
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="mobile_semester_{{ $semester }}_ipa">
                                <i class="fas fa-flask mr-1"></i> IPA
                            </label>
                            <input type="number" 
                                   id="mobile_semester_{{ $semester }}_ipa"
                                   name="semester_{{ $semester }}_ipa" 
                                   class="nilai-input form-control @error("semester_{$semester}_ipa") is-invalid @enderror" 
                                   value="{{ old("semester_{$semester}_ipa", $nilai['ipa']) }}"
                                   min="1" 
                                   max="100" 
                                   step="1"
                                   required
                                   data-semester="{{ $semester }}"
                                   data-mapel="ipa"
                                   oninput="calculateRataRata({{ $semester }})">
                            @error("semester_{$semester}_ipa")
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="mobile_semester_{{ $semester }}_ips">
                                <i class="fas fa-globe mr-1"></i> IPS
                            </label>
                            <input type="number" 
                                   id="mobile_semester_{{ $semester }}_ips"
                                   name="semester_{{ $semester }}_ips" 
                                   class="nilai-input form-control @error("semester_{$semester}_ips") is-invalid @enderror" 
                                   value="{{ old("semester_{$semester}_ips", $nilai['ips']) }}"
                                   min="1" 
                                   max="100" 
                                   step="1"
                                   required
                                   data-semester="{{ $semester }}"
                                   data-mapel="ips"
                                   oninput="calculateRataRata({{ $semester }})">
                            @error("semester_{$semester}_ips")
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-0">
                            <label><i class="fas fa-chart-line mr-1"></i> Rata-Rata</label>
                            <div class="rata-rata-display" id="mobile_rata_rata_{{ $semester }}">
                                {{ $nilai['rata_rata'] ? number_format($nilai['rata_rata'], 2) : '-' }}
                            </div>
                        </div>
                    </div>
                    @endforeach

                    <!-- Mobile Overall Average -->
                    <div class="semester-card" style="background: #f8f9fa; border: 2px solid #667eea;">
                        <h5 class="text-center mb-3" style="color: #28a745;">
                            <i class="fas fa-trophy mr-2"></i>Rata-Rata Keseluruhan
                        </h5>
                        <div class="rata-rata-display" id="mobile_rata_rata_keseluruhan" style="font-size: 24px; color: #28a745;">
                            {{ $calonSiswa->rata_rata_rapor ? number_format($calonSiswa->rata_rata_rapor, 2) : '-' }}
                        </div>
                    </div>

                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save mr-1"></i> Simpan Nilai Rapor
                    </button>
                    <a href="{{ route('pendaftar.dashboard') }}" class="btn btn-secondary btn-lg">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Info Card -->
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header bg-info">
                <h3 class="card-title text-white">
                    <i class="fas fa-calculator mr-2"></i>
                    Informasi Perhitungan Nilai
                </h3>
            </div>
            <div class="card-body">
                <h5>Komponen Penilaian Akhir PPDB:</h5>
                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="small-box bg-gradient-primary">
                            <div class="inner">
                                <h3>30%</h3>
                                <p>Nilai Rapor</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-book"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="small-box bg-gradient-success">
                            <div class="inner">
                                <h3>40%</h3>
                                <p>Tes CBT</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-laptop"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="small-box bg-gradient-warning">
                            <div class="inner">
                                <h3>30%</h3>
                                <p>Wawancara</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-comments"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="text-muted mb-0">
                    <i class="fas fa-info-circle mr-1"></i>
                    Nilai akhir = (Rata-rata Rapor × 30%) + (Nilai CBT × 40%) + (Nilai Wawancara × 30%)
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    // Calculate rata-rata on page load
    for (let i = 1; i <= 5; i++) {
        calculateRataRata(i);
    }
    calculateRataRataKeseluruhan();
    
    // Sync values between desktop and mobile inputs
    $('.nilai-input').on('input', function() {
        const name = $(this).attr('name');
        const value = $(this).val();
        
        // Update all inputs with the same name
        $(`input[name="${name}"]`).val(value);
    });
});

function calculateRataRata(semester) {
    const mtk = parseFloat($(`input[name="semester_${semester}_matematika"]`).val()) || 0;
    const ipa = parseFloat($(`input[name="semester_${semester}_ipa"]`).val()) || 0;
    const ips = parseFloat($(`input[name="semester_${semester}_ips"]`).val()) || 0;
    
    if (mtk > 0 && ipa > 0 && ips > 0) {
        const rataRata = (mtk + ipa + ips) / 3;
        $(`#rata_rata_${semester}`).text(rataRata.toFixed(2));
        $(`#mobile_rata_rata_${semester}`).text(rataRata.toFixed(2));
    } else {
        $(`#rata_rata_${semester}`).text('-');
        $(`#mobile_rata_rata_${semester}`).text('-');
    }
    
    // Recalculate overall average
    calculateRataRataKeseluruhan();
}

function calculateRataRataKeseluruhan() {
    let total = 0;
    let count = 0;
    
    for (let i = 1; i <= 5; i++) {
        const mtk = parseFloat($(`input[name="semester_${i}_matematika"]`).val()) || 0;
        const ipa = parseFloat($(`input[name="semester_${i}_ipa"]`).val()) || 0;
        const ips = parseFloat($(`input[name="semester_${i}_ips"]`).val()) || 0;
        
        if (mtk > 0 && ipa > 0 && ips > 0) {
            total += (mtk + ipa + ips) / 3;
            count++;
        }
    }
    
    if (count > 0) {
        const rataRataKeseluruhan = total / count;
        $('#rata_rata_keseluruhan').text(rataRataKeseluruhan.toFixed(2));
        $('#mobile_rata_rata_keseluruhan').text(rataRataKeseluruhan.toFixed(2));
    } else {
        $('#rata_rata_keseluruhan').text('-');
        $('#mobile_rata_rata_keseluruhan').text('-');
    }
}

// Validate input range
$('.nilai-input').on('input', function() {
    const val = parseInt($(this).val());
    if (val < 1) {
        $(this).val(1);
    } else if (val > 100) {
        $(this).val(100);
    }
});

// Form validation
$('#formNilaiRapor').on('submit', function(e) {
    let isValid = true;
    let emptyFields = [];
    
    $('.nilai-input').each(function() {
        const val = parseInt($(this).val());
        if (!val || val < 1 || val > 100) {
            isValid = false;
            $(this).addClass('is-invalid');
            emptyFields.push($(this).attr('name'));
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    if (!isValid) {
        e.preventDefault();
        toastr.error('Mohon isi semua nilai dengan benar (1-100)');
        return false;
    }
});
</script>
@endsection
