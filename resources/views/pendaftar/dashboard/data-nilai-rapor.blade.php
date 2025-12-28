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
        padding: 12px;
        text-align: center;
        font-weight: 600;
    }
    .nilai-table td {
        padding: 10px;
        text-align: center;
        vertical-align: middle;
    }
    .nilai-table tbody tr:hover {
        background-color: #f8f9fa;
    }
    .nilai-input {
        width: 100%;
        max-width: 100px;
        padding: 8px;
        border: 2px solid #e9ecef;
        border-radius: 6px;
        text-align: center;
        font-size: 16px;
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
        font-size: 18px;
        font-weight: bold;
        color: #667eea;
        padding: 10px;
        background: linear-gradient(135deg, #f0f4ff 0%, #e8eeff 100%);
        border-radius: 8px;
        text-align: center;
    }
    .info-box {
        background: linear-gradient(135deg, #fff8e6 0%, #fffbf0 100%);
        border-left: 4px solid #ffc107;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }
    .info-box ul {
        margin: 0.5rem 0 0 0;
        padding-left: 1.5rem;
    }
    .info-box li {
        margin-bottom: 0.5rem;
        color: #856404;
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
                        <h5 class="mb-2"><i class="fas fa-info-circle mr-2"></i>Petunjuk Pengisian:</h5>
                        <ul>
                            <li>Isi nilai rapor untuk <strong>Semester 1 sampai 5</strong></li>
                            <li>Mata pelajaran: <strong>Matematika, IPA, IPS</strong></li>
                            <li>Nilai harus dalam rentang <strong>1-100</strong> (tanpa koma)</li>
                            <li>Rata-rata akan dihitung otomatis dari 3 mata pelajaran</li>
                            <li>Nilai rapor akan menjadi salah satu komponen penilaian akhir</li>
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
});

function calculateRataRata(semester) {
    const mtk = parseFloat($(`input[name="semester_${semester}_matematika"]`).val()) || 0;
    const ipa = parseFloat($(`input[name="semester_${semester}_ipa"]`).val()) || 0;
    const ips = parseFloat($(`input[name="semester_${semester}_ips"]`).val()) || 0;
    
    if (mtk > 0 && ipa > 0 && ips > 0) {
        const rataRata = (mtk + ipa + ips) / 3;
        $(`#rata_rata_${semester}`).text(rataRata.toFixed(2));
    } else {
        $(`#rata_rata_${semester}`).text('-');
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
    } else {
        $('#rata_rata_keseluruhan').text('-');
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
