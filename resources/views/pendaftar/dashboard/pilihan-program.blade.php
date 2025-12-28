@extends('layouts.pendaftar')

@section('title', 'Pilihan Program')
@section('page-title', 'Pilihan Program / Jurusan')

@section('breadcrumb')
<li class="breadcrumb-item active">Pilihan Program</li>
@endsection

@section('css')
<style>
    .program-option-card {
        border: 2px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
        background-color: #fff;
    }

    .program-option-card:hover {
        border-color: #007bff;
        box-shadow: 0 4px 8px rgba(0,123,255,0.15);
        transform: translateY(-2px);
    }

    .program-option-card.selected {
        border-color: #28a745;
        background-color: #d4edda;
        box-shadow: 0 4px 12px rgba(40,167,69,0.3);
    }

    .program-option-card input[type="radio"] {
        position: absolute;
        opacity: 0;
    }

    .program-card-content {
        text-align: center;
        padding: 10px;
    }

    .program-card-content h5 {
        font-weight: 600;
        color: #333;
    }

    .program-option-card.selected .program-card-content h5 {
        color: #155724;
    }

    .program-option-card.selected .program-card-content i {
        color: #28a745 !important;
    }

    #btnSimpan {
        font-size: 1.1rem;
        padding: 12px;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-10 offset-md-1">
        {{-- Info Card --}}
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Informasi</h3>
            </div>
            <div class="card-body">
                <p><strong>Jalur Pendaftaran:</strong> {{ $jalur->nama }}</p>
                <p><strong>Tipe Pilihan:</strong> 
                    @if($jalur->pilihan_program_tipe === 'reguler_asrama')
                        <span class="badge badge-primary">Reguler / Asrama</span>
                    @elseif($jalur->pilihan_program_tipe === 'jurusan')
                        <span class="badge badge-success">Pilihan Jurusan</span>
                    @else
                        <span class="badge badge-secondary">Pilihan Program</span>
                    @endif
                </p>
                
                @if($jalur->pilihan_program_catatan)
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle"></i> <strong>Catatan Penting:</strong>
                    <div class="mt-2">{!! nl2br(e($jalur->pilihan_program_catatan)) !!}</div>
                </div>
                @endif

                @if($calonSiswa->pilihan_program)
                <div class="alert alert-success mt-3">
                    <i class="fas fa-check-circle"></i> <strong>Pilihan Anda saat ini:</strong> 
                    <span class="font-weight-bold">{{ $calonSiswa->pilihan_program }}</span>
                    <br>
                    <small class="text-muted">Anda dapat mengubah pilihan sebelum melakukan finalisasi pendaftaran</small>
                </div>
                @endif
            </div>
        </div>

        {{-- Selection Card --}}
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list-ul"></i> Pilih Program</h3>
            </div>
            <div class="card-body">
                <form id="formPilihanProgram">
                    @csrf
                    <div class="row">
                        @forelse($jalur->pilihan_program_options ?? [] as $option)
                        <div class="col-md-6 mb-3">
                            <div class="custom-control custom-radio program-option-card 
                                {{ $calonSiswa->pilihan_program === $option ? 'selected' : '' }}"
                                data-value="{{ $option }}">
                                <input class="custom-control-input" 
                                       type="radio" 
                                       id="option_{{ $loop->index }}" 
                                       name="pilihan_program" 
                                       value="{{ $option }}"
                                       {{ $calonSiswa->pilihan_program === $option ? 'checked' : '' }}>
                                <label class="custom-control-label w-100" for="option_{{ $loop->index }}">
                                    <div class="program-card-content">
                                        <i class="fas fa-graduation-cap fa-2x text-primary mb-2"></i>
                                        <h5 class="mb-0">{{ $option }}</h5>
                                    </div>
                                </label>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                Belum ada pilihan program yang tersedia. Silakan hubungi panitia.
                            </div>
                        </div>
                        @endforelse
                    </div>

                    @if(count($jalur->pilihan_program_options ?? []) > 0)
                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-primary btn-lg btn-block" id="btnSimpan">
                            <i class="fas fa-save"></i> Simpan Pilihan
                        </button>
                    </div>
                    @endif
                </form>
            </div>
        </div>

        {{-- Back Button --}}
        <div class="text-center mb-4">
            <a href="{{ route('pendaftar.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    // Click card to select radio
    $('.program-option-card').on('click', function() {
        const radio = $(this).find('input[type="radio"]');
        radio.prop('checked', true);
        
        // Remove selected class from all cards
        $('.program-option-card').removeClass('selected');
        
        // Add selected class to clicked card
        $(this).addClass('selected');
    });

    // Form submit handler
    $('#formPilihanProgram').on('submit', function(e) {
        e.preventDefault();
        
        const selectedValue = $('input[name="pilihan_program"]:checked').val();
        
        if (!selectedValue) {
            toastr.warning('Silakan pilih salah satu program terlebih dahulu');
            return;
        }

        // Disable button and show loading
        const btnSimpan = $('#btnSimpan');
        const originalText = btnSimpan.html();
        btnSimpan.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

        $.ajax({
            url: "{{ route('pendaftar.pilihan-program.store') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                pilihan_program: selectedValue
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    
                    // Redirect to dashboard after 1.5 seconds
                    setTimeout(function() {
                        window.location.href = "{{ route('pendaftar.dashboard') }}";
                    }, 1500);
                } else {
                    toastr.error(response.message || 'Terjadi kesalahan');
                    btnSimpan.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr) {
                let errorMsg = 'Terjadi kesalahan saat menyimpan';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    errorMsg = Object.values(errors).flat().join('<br>');
                }
                
                toastr.error(errorMsg);
                btnSimpan.prop('disabled', false).html(originalText);
            }
        });
    });
});
</script>
@endsection
