@extends('layouts.pendaftar')

@section('title', 'Pilihan Program')
@section('page-title', 'Pilihan Program / Jurusan')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('pendaftar.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Pilihan Program</li>
@endsection

@section('css')
<style>
    .info-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .program-option-card {
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        padding: 1.5rem;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
        position: relative;
        overflow: hidden;
        height: 100%;
        min-height: 160px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .program-option-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .program-option-card:hover {
        border-color: #667eea;
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.2);
        transform: translateY(-4px);
    }

    .program-option-card:hover::before {
        transform: scaleX(1);
    }

    .program-option-card.selected {
        border-color: #28a745;
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        box-shadow: 0 8px 24px rgba(40, 167, 69, 0.3);
    }

    .program-option-card.selected::before {
        background: #28a745;
        transform: scaleX(1);
    }

    .program-option-card input[type="radio"] {
        position: absolute;
        opacity: 0;
    }

    .program-card-content {
        text-align: center;
    }

    .program-card-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
        color: #667eea;
        transition: all 0.3s ease;
    }

    .program-option-card:hover .program-card-icon {
        transform: scale(1.1);
    }

    .program-option-card.selected .program-card-icon {
        color: #28a745;
    }

    .program-card-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #333;
        margin: 0;
    }

    .program-option-card.selected .program-card-title {
        color: #155724;
    }

    .selected-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #28a745;
        color: white;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        opacity: 0;
        transform: scale(0);
        transition: all 0.3s ease;
    }

    .program-option-card.selected .selected-badge {
        opacity: 1;
        transform: scale(1);
    }

    .btn-save {
        padding: 0.75rem 2rem;
        font-size: 1.1rem;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
    }

    .current-selection-alert {
        border-left: 4px solid #28a745;
        background: linear-gradient(90deg, #d4edda 0%, #f0fdf4 100%);
    }

    @media (max-width: 768px) {
        .info-section {
            padding: 1rem;
        }
        
        .program-option-card {
            min-height: 140px;
            padding: 1rem;
        }
        
        .program-card-icon {
            font-size: 2.5rem;
        }
        
        .program-card-title {
            font-size: 1.1rem;
        }
    }

    @media (max-width: 576px) {
        .program-option-card {
            min-height: 120px;
        }
        
        .program-card-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .program-card-title {
            font-size: 1rem;
        }
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        @if($calonSiswa->is_finalisasi)
        <div class="alert alert-warning">
            <h5><i class="fas fa-lock mr-2"></i>Pilihan Program Sudah Difinalisasi</h5>
            <p class="mb-0">Pilihan program Anda sudah difinalisasi dan tidak dapat diubah. Jika terdapat kesalahan, silakan hubungi panitia.</p>
        </div>
        @endif
        
        {{-- Info Header --}}
        <div class="info-section">
            <div class="row align-items-center">
                <div class="col-md-8 col-12 mb-3 mb-md-0">
                    <h4 class="mb-2"><i class="fas fa-graduation-cap mr-2"></i>{{ $jalur->nama }}</h4>
                    <p class="mb-0">
                        <span class="badge badge-light">
                            @if($jalur->pilihan_program_tipe === 'reguler_asrama')
                                <i class="fas fa-bed mr-1"></i>Reguler / Asrama
                            @elseif($jalur->pilihan_program_tipe === 'jurusan')
                                <i class="fas fa-book mr-1"></i>Pilihan Jurusan
                            @else
                                <i class="fas fa-stream mr-1"></i>Pilihan Program
                            @endif
                        </span>
                    </p>
                </div>
                <div class="col-md-4 col-12 text-md-right">
                    @if($calonSiswa->is_finalisasi)
                    <div class="badge badge-danger px-3 py-2" style="font-size: 0.9rem;">
                        <i class="fas fa-lock mr-1"></i>
                        <span class="d-none d-sm-inline">Sudah Difinalisasi</span>
                        <span class="d-inline d-sm-none">Terkunci</span>
                    </div>
                    @else
                    <div class="badge badge-warning px-3 py-2" style="font-size: 0.9rem;">
                        <i class="fas fa-clock mr-1"></i>
                        <span class="d-none d-sm-inline">Dapat diubah sebelum finalisasi</span>
                        <span class="d-inline d-sm-none">Bisa diubah</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Current Selection Alert --}}
        @if($calonSiswa->pilihan_program)
        <div class="alert current-selection-alert" role="alert">
            <div class="d-flex align-items-center">
                <div class="mr-3">
                    <i class="fas fa-check-circle fa-2x text-success"></i>
                </div>
                <div class="flex-grow-1">
                    <h5 class="alert-heading mb-1">Pilihan Anda Saat Ini</h5>
                    <p class="mb-1">
                        <strong class="text-success" style="font-size: 1.1rem;">{{ $calonSiswa->pilihan_program }}</strong>
                    </p>
                    <small class="text-muted">
                        <i class="fas fa-info-circle mr-1"></i>
                        Anda dapat mengubah pilihan dengan memilih opsi lain di bawah
                    </small>
                </div>
            </div>
        </div>
        @endif

        {{-- Important Notes --}}
        @if($jalur->pilihan_program_catatan)
        <div class="alert alert-info" role="alert">
            <h5 class="alert-heading">
                <i class="fas fa-info-circle mr-2"></i>Catatan Penting
            </h5>
            <div class="mt-2" style="white-space: pre-line;">{{ $jalur->pilihan_program_catatan }}</div>
        </div>
        @endif

        {{-- Selection Card --}}
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h3 class="card-title mb-0">
                    <i class="fas fa-list-ul mr-2 text-primary"></i>
                    Silakan Pilih Program
                </h3>
            </div>
            <div class="card-body">
                <form id="formPilihanProgram">
                    @csrf
                    
                    @if(count($jalur->pilihan_program_options ?? []) > 0)
                    <div class="row">
                        @foreach($jalur->pilihan_program_options as $option)
                        <div class="col-md-6 col-12 mb-3">
                            <div class="program-option-card {{ $calonSiswa->pilihan_program === $option ? 'selected' : '' }}"
                                data-value="{{ $option }}">
                                <input class="custom-control-input" 
                                       type="radio" 
                                       id="option_{{ $loop->index }}" 
                                       name="pilihan_program" 
                                       value="{{ $option }}"
                                       {{ $calonSiswa->pilihan_program === $option ? 'checked' : '' }}>
                                <label class="mb-0 w-100" for="option_{{ $loop->index }}" style="cursor: pointer;">
                                    <div class="program-card-content">
                                        <div class="program-card-icon">
                                            @if($jalur->pilihan_program_tipe === 'reguler_asrama')
                                                @if(strtolower($option) === 'reguler')
                                                    <i class="fas fa-user-graduate"></i>
                                                @else
                                                    <i class="fas fa-bed"></i>
                                                @endif
                                            @elseif($jalur->pilihan_program_tipe === 'jurusan')
                                                <i class="fas fa-book-open"></i>
                                            @else
                                                <i class="fas fa-graduation-cap"></i>
                                            @endif
                                        </div>
                                        <h5 class="program-card-title">{{ $option }}</h5>
                                    </div>
                                    <div class="selected-badge">
                                        <i class="fas fa-check"></i>
                                    </div>
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    @if(!$calonSiswa->is_finalisasi)
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary btn-save btn-lg" id="btnSimpan">
                            <i class="fas fa-save mr-2"></i>Simpan Pilihan
                        </button>
                    </div>
                    @else
                    <div class="alert alert-info text-center mt-4 mb-0">
                        <i class="fas fa-info-circle"></i> Pilihan tidak dapat diubah karena data sudah difinalisasi
                    </div>
                    @endif
                    @else
                    <div class="alert alert-warning text-center" role="alert">
                        <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                        <h5>Belum Ada Pilihan Tersedia</h5>
                        <p class="mb-0">Silakan hubungi panitia untuk informasi lebih lanjut.</p>
                    </div>
                    @endif
                </form>
            </div>
        </div>

        {{-- Back Button --}}
        <div class="text-center mt-3 mb-4">
            <a href="{{ route('pendaftar.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Dashboard
            </a>
        </div>
    </div>

    {{-- Sidebar Info (Desktop only) --}}
    <div class="col-lg-4 d-none d-lg-block">
        <div class="card shadow-sm sticky-top" style="top: 20px;">
            <div class="card-header bg-gradient-info text-white">
                <h5 class="mb-0"><i class="fas fa-question-circle mr-2"></i>Bantuan</h5>
            </div>
            <div class="card-body">
                <h6 class="font-weight-bold mb-3">Cara Memilih Program:</h6>
                <ol class="pl-3">
                    <li class="mb-2">Klik pada kartu program yang Anda inginkan</li>
                    <li class="mb-2">Kartu akan berubah warna menjadi hijau</li>
                    <li class="mb-2">Klik tombol "Simpan Pilihan"</li>
                    <li class="mb-2">Pilihan dapat diubah sebelum finalisasi</li>
                </ol>
                
                <hr>
                
                <h6 class="font-weight-bold mb-3">Tips:</h6>
                <ul class="pl-3 text-muted" style="font-size: 0.9rem;">
                    <li class="mb-2">Pertimbangkan minat dan bakat Anda</li>
                    <li class="mb-2">Konsultasikan dengan orang tua</li>
                    <li class="mb-2">Pastikan pilihan sesuai dengan rencana masa depan</li>
                </ul>

                <div class="alert alert-warning mt-3 mb-0" role="alert">
                    <small>
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        <strong>Penting:</strong> Pilihan akan dikunci setelah finalisasi pendaftaran
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    @if($calonSiswa->is_finalisasi)
    // Disable all interactions when finalized
    $('.program-option-card').css({
        'cursor': 'not-allowed',
        'opacity': '0.7'
    });
    $('input[name="pilihan_program"]').prop('disabled', true);
    @else
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
        btnSimpan.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...');

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
    @endif
});
</script>
@endsection
