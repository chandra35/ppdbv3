@extends('adminlte::page')

@section('title', 'Pengaturan Bobot Nilai')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">
            <i class="fas fa-balance-scale mr-2"></i>Pengaturan Bobot Nilai
        </h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.nilai-seleksi.index') }}">Nilai Seleksi</a></li>
            <li class="breadcrumb-item active">Bobot Nilai</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-sliders-h mr-2"></i>Atur Bobot Komponen Penilaian
                    </h3>
                </div>
                <form action="{{ route('admin.nilai-seleksi.bobot.update') }}" method="POST" id="bobotForm">
                    @csrf
                    <div class="card-body">
                        <div class="callout callout-info">
                            <h5><i class="fas fa-info-circle mr-2"></i>Informasi</h5>
                            <p class="mb-0">
                                Total bobot harus sama dengan <strong>100%</strong>. 
                                Bobot digunakan untuk menghitung total nilai akhir peserta.
                            </p>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="50">No</th>
                                        <th>Komponen Penilaian</th>
                                        <th width="150">Bobot (%)</th>
                                        <th width="100">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bobotList as $index => $bobot)
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td>
                                                <input type="hidden" name="bobot[{{ $index }}][id]" value="{{ $bobot->id }}">
                                                <strong>{{ $bobot->nama_komponen }}</strong>
                                                <br><small class="text-muted">{{ $bobot->komponen }}</small>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <input type="number" 
                                                           name="bobot[{{ $index }}][bobot]" 
                                                           class="form-control bobot-input" 
                                                           value="{{ $bobot->bobot }}"
                                                           min="0" max="100" step="0.01"
                                                           required>
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">%</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" 
                                                           class="custom-control-input" 
                                                           id="active_{{ $index }}"
                                                           name="bobot[{{ $index }}][is_active]"
                                                           value="1"
                                                           {{ $bobot->is_active ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="active_{{ $index }}">Aktif</label>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-light">
                                        <td colspan="2" class="text-right"><strong>Total Bobot:</strong></td>
                                        <td>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="totalBobot" readonly>
                                                <div class="input-group-append">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div id="bobotWarning" class="alert alert-danger" style="display: none;">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Total bobot harus sama dengan 100%!
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary" id="btnSubmit">
                            <i class="fas fa-save mr-1"></i>Simpan Perubahan
                        </button>
                        <a href="{{ route('admin.nilai-seleksi.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i>Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-calculator mr-2"></i>Rumus Perhitungan</h3>
                </div>
                <div class="card-body">
                    <p>Total Nilai dihitung dengan rumus:</p>
                    <div class="bg-light p-3 rounded">
                        <code>
                            Total = Σ (Nilai × Bobot / 100)
                        </code>
                    </div>
                    
                    <hr>
                    
                    <p><strong>Contoh:</strong></p>
                    <ul class="list-unstyled">
                        <li>Wawancara: 80 × 25% = 20</li>
                        <li>Baca Qur'an: 85 × 25% = 21.25</li>
                        <li>Tulis Qur'an: 75 × 25% = 18.75</li>
                        <li>Hafalan: 90 × 25% = 22.5</li>
                    </ul>
                    <p class="mb-0"><strong>Total = 82.5</strong></p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-history mr-2"></i>Informasi</h3>
                </div>
                <div class="card-body">
                    <p><strong>Tahun Pelajaran:</strong><br>{{ $tahunPelajaran->nama }}</p>
                    <p class="mb-0"><strong>Terakhir Diubah:</strong><br>{{ $bobotList->first()?->updated_at?->format('d M Y H:i') ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    function calculateTotal() {
        var total = 0;
        $('.bobot-input').each(function() {
            var checkbox = $(this).closest('tr').find('input[type="checkbox"]');
            if (checkbox.is(':checked')) {
                total += parseFloat($(this).val()) || 0;
            }
        });
        
        $('#totalBobot').val(total.toFixed(2));
        
        if (Math.abs(total - 100) > 0.01) {
            $('#bobotWarning').show();
            $('#totalBobot').addClass('is-invalid');
            $('#btnSubmit').prop('disabled', true);
        } else {
            $('#bobotWarning').hide();
            $('#totalBobot').removeClass('is-invalid').addClass('is-valid');
            $('#btnSubmit').prop('disabled', false);
        }
    }

    // Calculate on load
    calculateTotal();

    // Calculate on input change
    $('.bobot-input, input[type="checkbox"]').on('change input', function() {
        calculateTotal();
    });

    // Form validation before submit
    $('#bobotForm').on('submit', function(e) {
        var total = parseFloat($('#totalBobot').val());
        if (Math.abs(total - 100) > 0.01) {
            e.preventDefault();
            alert('Total bobot harus sama dengan 100%!');
            return false;
        }
    });
});
</script>
@stop
